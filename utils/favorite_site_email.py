import pymysql
import smtplib
from datetime import datetime, timedelta, time
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText

def openDB():
    """
    This functions opens a connection to the database.  Make sure
    to update the credentials below, grabbing them for an external
    configuration file.
    return db:  database connection
    rtype db: pymysql.connections.Connection
    return cur:  database cursor
    rtype cur:  pymysql.cursors.DictCursor
    """
    db = pymysql.connect(host="hostname", user="username", passwd="password", db="dbname")
    cur = db.cursor()

    return (db, cur)

def findSAMs(cur, db):
    """
    This function finds SAMs that occurred within the last week.
    param db:  database connection
    type db:  pymysql.connections.Connection
    param cur:  database cursor
    type cur:  pymysql.cursors.DictCursor
    return selectionIDs:  list of selection IDs
    rtype selectionIDs:  list
    """
    endDate = datetime.combine((datetime.now() - timedelta(weeks=6,days=1)).date(), time(23,59,59))
    startDate = datetime.combine((endDate - timedelta(days=6)).date(), time(0,0,0))

    sql = 'SELECT selectionID FROM selectedTargets WHERE targetTimeStart >= "' + startDate.strftime('%Y-%m-%d %H:%M:%S') + '" and targetTimeStart <= "' + endDate.strftime('%Y-%m-%d %H:%M:%S') + '"'
    cur.execute(sql)
    info = cur.fetchall()

    selectionIDs = []
    for thisSelectionID in info:
        selectionIDs.append(thisSelectionID[0])

    return selectionIDs

def userListGenerator(cur, db, selectionIDs):
    """
    This function clooks at each selectionID and finds users who
    marked that selection's  site as their "favorite".
    param db:  database connection
    type db:  pymysql.connections.Connection
    param cur:  database cursor
    type cur:  pymysql.cursors.DictCursor
    param selectionIDs:  list of selectionIDs in the past week
    type selectionIDs:  list
    return userSAMs:  a dict of the SAM and user info for each selectionID
    rtype userSAMs:  dict
    """    
    userSAMs = {}
    for thisID in selectionIDs:
        sql="select s.selectionID, s.targetTimeStart, s.targetTimeEnd, t.targetID, t.name, u.uID, u.email, CONCAT(u.firstName, ' ', u.lastName) AS name from selectedTargets s, sites t, users u, users_sites x where s.selectionID=%s and s.targetID=t.targetID and u.uID=x.uID and t.targetID=x.targetID AND s.display=1" % thisID
        cur.execute(sql)
        info = cur.fetchall()

        for thisEntry in info:
            name = thisEntry[7]
            if name not in userSAMs:
                uID = thisEntry[5]
                email = thisEntry[6]
                samLine = [thisEntry[0],thisEntry[1],thisEntry[2],thisEntry[3],thisEntry[4]]
                userSAMs[name] = { 'sams' : [samLine], 'email' : email, 'uID' : uID }
            else:
                samLine = [thisEntry[0],thisEntry[1],thisEntry[2],thisEntry[3],thisEntry[4]]
                userSAMs[name]['sams'].append(samLine)

    return userSAMs

def emailNotices(cur, db, userSAMs):
    """
    For each SAM over the last week, this function will send out an email to 
    users who marked the SAM's site as their "favorite".
    param db:  database connection
    type db:  pymysql.connections.Connection
    param cur:  database cursor
    type cur:  pymysql.cursors.DictCursor
    param userSAMs:  a dict of the SAM and user info for each selectionID
    type userSAMs:  dict
    """  
    for username in userSAMs:
        server = smtplib.SMTP('localhost',25)
        server.ehlo()

        fromaddr = 'noreply@yourwebsite.com'
        toaddr = userSAMs[username]['email']
        subject =  'New data available for your favorite sites'

        msg = MIMEMultipart()
        msg['From'] = fromaddr
        msg['To'] = toaddr
        msg['Subject'] = subject

        body = 'New data has been added to the SAM website for the following sites:'
        body += '<ul>'
        for thisSAM in userSAMs[username]['sams']:
            start = thisSAM[1].strftime('%Y-%m-%d %H:%M:%S')
            end = thisSAM[2].strftime('%Y-%m-%d %H:%M:%S')
            targetID = thisSAM[3]
            targetName = thisSAM[4]
            body += '<li><b>' + targetID + ' (' + targetName + '):</b> ' + start + ' to ' + end + '</li>'
        body += '</ul>'
        body += '<br/>'
        body += 'You can find plots and subset the data on your favorites page at <a href="https://yourwebsite.com/favorites/index.php">https://yourwebsite.com/favorites/index.php</a>.'
        body += '<br/><br/>'
        body += 'The Project is interested to know how you are using our data and about your scientific findings. Please share your results and publications with us at <a href="mailto:feedback@yourwebsite.com">feedback@yourwebsite</a>. If you publish, please acknowledge the project with this statement "Acknowledgement."'

        msg.attach(MIMEText(body, 'html', 'utf-8'))
        text = msg.as_string()
        try:
            server.sendmail(fromaddr, toaddr, text)
            server.close
            print('Message sent')
        except:
            print('Message failed to send.')

    return


def closeDB(db):
    """
    This function will close the database connection.
    param db:  database connection
    type db:  pymysql.connections.Connection
    """
    db.close()

    return


if __name__ == '__main__':
    db, cur = openDB()
    selectionIDs = findSAMs(cur, db)
    userSAMs = userListGenerator(cur, db, selectionIDs)
    emailNotices(cur, db, userSAMs)
    closeDB(db)
