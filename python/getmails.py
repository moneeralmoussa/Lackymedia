import sys
import yaml, imaplib, json, csv
from emailinterface import mailextractor
from StringIO import StringIO

def extractcsv(data):
    extr = mailextractor(emailstr=data[0][1])

    if not 'Automatischer Bericht FMS-Fahrerbericht' in extr.getSubject():
        return {'error':'hat kein zweck'}

    ok = False

    ret = {}

    for subtype in ['csv']: #,'html','plain']:
        try:
            for f in extr.getFilenames(subtype):
                buff = StringIO(extr.getFile(f))
                lines = csv.reader(buff)
                curperson = False
                oldline = ['', '', '', '', '', '', '', '']

                for line in lines:
                    if line[0] == 'Anfangsdatum':
                        ret['begin'] = line[1]
                    elif line[0] == 'End-Datum':
                        ret['end'] = line[1]
                    elif line[0]+line[1]+line[2] == '':
                        curperson = oldline[6]
                        if curperson == '':
                            curperson = oldline[0]
                        ret[curperson] = {'name':oldline[0]}
                    elif curperson:
                        ret[curperson][line[0]] = {
                            'dauer':line[3].replace('.', '').replace(',', '.'),
                            'betrag':line[4].replace('.', '').replace(',', '.'),
                            'strecke':line[5].replace('.', '').replace(',', '.'),
                            'wert':line[6].replace('.', '').replace(',', '.')
                        }
                    oldline = line[:]
                ok = True
            break
        except:
            ret['error'] = subtype + " wars's nicht."

    if not ok:
        ret['error'] = "entpacken fehlgeschlagen."

    return ret

with open("../app/config/parameters.yml", "r") as stream:
    try:
        parameters = yaml.load(stream)
        msgidliste=[]
        ret=[]

        M=imaplib.IMAP4_SSL(parameters["parameters"]["mailer_host"])
        M.login(parameters["parameters"]["mailer_user"],parameters["parameters"]["mailer_password"])

        M.select('INBOX')
        status,msgnums = M.search(None,'FROM','"noreply@trimbletl.com"')
        for num in msgnums[0].split():
            typ, data = M.fetch(num, '(RFC822)')
            ret.append(extractcsv(data))
            msgidliste.append(num)

        message_set=','.join(msgidliste)

        if message_set:
            M.copy(message_set, 'Verarbeitet')
            M.store(message_set, '+FLAGS', '\\Deleted')
            M.expunge()

        M.close()
        M.logout()

        print(json.dumps(ret, sort_keys=True))
    except:
        print(json.dumps(False))
