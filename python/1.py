import csv
import mysql.connector 
from pprint import pprint
mydb = mysql.connector.connect(host='localhost',
    user='root',
    passwd='',
    db='8')
cursor = mydb.cursor()
i = 'false'
with open('AuswertungStatus164.csv') as csvfile:
    lines = csv.reader(csvfile, delimiter=';', quotechar='"')
    cursor.execute('DELETE  FROM moneer')
    mydb.commit()  
    for row in lines:
        if i == 'false':
                print i
                i = 'true'
        else:
                mySql_insert_query = """INSERT INTO moneer(id) 
                                VALUES (%s) """
                #LKW COE-L 637
                #TOURID  
                #TOURNUMMER  
                #RECHNUNGSEMPFAENGER EifHel01
                #BELADEORT
                #EMPFANGSORTE
                #ANZAHLAUFTRAEGE
                #MINAUFTRAGSTERMIN
                #MAXAUFTRAGSTERMIN
                #GEPLTOURBEGINN
                #GEPLERLOES
                #TATSTOURBEGINN
                #GEPRUEFT
                #FAHRZNAME 637 Dispo Nicole
                #FAHRZEUGGRUPPE MTW / Kran
                recordTuple = (row[1],)
                cursor.execute(mySql_insert_query, recordTuple)
                mydb.commit()  
                print  row[1]
#close the connection to the database.
        
      
cursor.close()
print ("Done")