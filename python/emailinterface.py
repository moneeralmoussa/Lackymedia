import email
import sys
from email.Header import decode_header
class mailextractor(object):
    """
    This class is capable of extracting attachments from email-messages provided
    on stdin while instantiating the class.
    """

    def __init__(self, emailfh = None, emailstr = None):
        if emailfh:
            self.__emailmsg = email.message_from_file(emailfh)
        elif emailstr:
            self.__emailmsg = email.message_from_string(emailstr)
        else:
            self.__emailmsg = email.message_from_file(sys.stdin)
        self.__msgparts = None

    def _decodeFilename(self, fname):
        parts = decode_header(fname)
#        print parts
        if parts:
            subparts = parts[0]
#            print subparts[1]
            if subparts[1] == 'iso-8859-1' or not subparts[1]:
#                print 'subparts', subparts[0]
                return subparts[0]
        else:
#            print 'keine parts'
            return None

    def getSubject(self):
        return self.__emailmsg['Subject']

    def extractParts(self,stype):
        if not self.__emailmsg.is_multipart():
            print 'kein multipart'
            return None
        msgs = self.__emailmsg.get_payload()
        msgdata = {}
        for msg in msgs:
#            print msg.get_content_subtype()
            if msg.get_filename(None):
#                print msg.get_content_subtype()
                if msg.get_content_subtype() == stype:
#                    print msg
                    msgdata[self._decodeFilename(msg.get_filename())] = msg.get_payload(decode = True)
            elif stype == 'html':
                if msg.get_content_subtype() == stype:
                    msgdata['html.htm'] = msg.get_payload(decode = True)
            elif stype == 'plain':
                if msg.get_content_subtype() == stype:
                    msgdata['plain.txt'] = msg.get_payload(decode = True)
        return msgdata

    def getFilenames(self,stype):
        self.__msgparts = self.extractParts(stype)
        if self.__msgparts:
            return self.__msgparts.keys()
        else:
#            print 'kein dateiname'
            return None

    def getFile(self, fname):
        if not self.__msgparts.has_key(fname):
            return None
        return self.__msgparts[fname]
