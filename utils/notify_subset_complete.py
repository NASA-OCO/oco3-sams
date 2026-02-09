import json
import smtplib
import pymysql
import requests
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText

def openDB():
    """
    This functions opens a connection to the database.  Make sure
    to update the credentials below, grabbing them from an external
    configuration file.
    return db:  database connection
    rtype db: pymysql.connections.Connection
    return cur:  database cursor
    rtype cur:  pymysql.cursors.DictCursor
    """
    db = pymysql.connect(host="hostname",
                         user="username",
                         passwd="password",
                         db="dbname")

    cur = db.cursor()

    return (db, cur)


def closeDB(db):
    """
    This function will close the database connection.
    param db:  database connection
    type db:  pymysql.connections.Connection
    """
    db.close()
    
    return


def checkJobStatus(db, cur):
    """
    This function checks the status of a subset jobs that have not beeen 
    marked as "Succeeded" in the database.  The goal is to find the job IDs
    of jobs that have not yet completed and users have not been notified.
    param db:  database connection
    type db:  pymysql.connections.Connection
    param cur:  database cursor
    type cur:  pymysql.cursors.DictCursor
    return numOfRows:  number of rows 
    rtype numOfRows:  int
    """
    sql = 'SELECT jID FROM jobs WHERE notified=0 AND status != "Succeeded" LIMIT 15'
    cur.execute(sql)
    cur.fetchall()
    numOfRows = cur.rowcount

    return numOfRows

def updateJobStatus(db, cur):
    """
    This function checks the GES-DISC's subset endpoint to update the current status
    of a running job in the database.
    param db:  database connection
    type db:  pymysql.connections.Connection
    param cur:  database cursor
    type cur:  pymysql.cursors.DictCursor
    """
    sql = 'SELECT jID, jobID, sessionID, status FROM jobs WHERE notified=0 AND status != "Succeeded"'
    cur.execute(sql)
    jobs = cur.fetchall()

    for thisJob in jobs:
        headers = {
            'Accept': 'application/json, text/plain, */*',
            'Content-Type': 'application/json;charset=utf-8',
        }

        data = '{"methodname": "GetStatus", "args": {"jobId": "' + thisJob[1] + '", "sessionId": "' + thisJob[2] + '" }, "type": "jsonwsp/request", "version": "1.0"}'

        response = requests.post('https://disc.gsfc.nasa.gov/service/subset/jsonwsp', headers=headers, data=data)

        try:
            r = json.loads(response.text)
            status = r['result']['Status']

            if thisJob[3] != status:
                sql = 'UPDATE jobs SET status="' + status + '" WHERE jID="' + str(thisJob[0]) + '"'
                cur.execute(sql)
                db.commit()
        except KeyError:
            print('Something is incorrect with job ID %s' % thisJob[0])

    return

def notifyOfSuccess(db,cur):
    """
    When a job is marked as "Succeeded" in the database and the
    user has not been notified of its completion, this function
    will send out an email to them.
    param db:  database connection
    type db:  pymysql.connections.Connection
    param cur:  database cursor
    type cur:  pymysql.cursors.DictCursor
    """
    sql = 'SELECT j.jID, j.jobID, j.sessionID, u.email, j.parameters, j.targetID FROM jobs j, users u WHERE j.uID=u.uID AND notified=0 AND status="Succeeded" ORDER BY j.jobID ASC'
    cur.execute(sql)
    successes = cur.fetchall()

    for thisSuccess in successes:
        jsonInfo = json.loads(thisSuccess[4])
        headers = {
            'Accept': 'application/json, text/plain, */*',
            'Content-Type': 'application/json;charset=utf-8',
        }

        data = '{"methodname": "GetResult", "args": {"jobId": "' + thisSuccess[1] + '", "sessionId": "' + thisSuccess[2] + '" }, "type": "jsonwsp/request", "version": "1.0"}'

        response = requests.post('https://disc.gsfc.nasa.gov/service/subset/jsonwsp', headers=headers, data=data)

        r = json.loads(response.text)
        try:
            expires = r['result']['expires']
            expiredDateTime = expires.split('T')[0] + ' ' + expires.split('T')[1][:-5]
            items = r['result']['items']
            link = items[-1]['link']
            filename = items[-1]['label']
        except (KeyError, IndexError):
            print('Bad Job ID: %s' % thisSuccess[1])
            continue

        body = 'Your subsetted file request for <b>' + thisSuccess[5] + '</b> from <b>' + jsonInfo['args']['start'] + '</b> to <b>' + jsonInfo['args']['end'] + '</b> is ready for download.  It will be availalbe for the next 48 hours.'
        body += '<br/><br/>'
        body += 'File: <a href="' + link + '">' + filename + '</a>'
        body += '<br/><br/>'
        body += 'Subset Parameters: ' + thisSuccess[4]
        body += '<br/><br/>'
        body += '<i>For additional information, please visit the <a href="https://yourwebsite.com">Website</a></i><br/>'
        body += '<br/><br/>'
        body += '<i>Note that to download your file, you may be prompted to login to the NASA Earthdata system.  If you do not have a NASA Earthdata account, you may set one up <a href="https://urs.earthdata.nasa.gov/home">here</a>.'

        server = smtplib.SMTP('localhost',25) #smtp,port number
        server.ehlo()

        fromaddr = 'noreply@mail.com'
        toaddr = [thisSuccess[3]]
        subject = 'Subset File Ready'

        msg = MIMEMultipart()
        msg['From'] = fromaddr
        msg['To'] = ', '.join(toaddr)
        msg['Subject'] = subject
        msg.attach(MIMEText(body, 'html', 'utf-8'))
        text = msg.as_string()

        server.sendmail(fromaddr, toaddr, text)
        server.close
        print('Message sent')

        sql = 'UPDATE jobs SET notified=1, expireDateTime="' + expiredDateTime + '" WHERE jID=' + str(thisSuccess[0])
        cur.execute(sql)
        db.commit()

    return

if __name__ == "__main__":
    db,cur = openDB()
    numOfRows = checkJobStatus(db, cur)
    if numOfRows > 0:
        updateJobStatus(db,cur)
        notifyOfSuccess(db,cur)
    closeDB(db)
