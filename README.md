# Financial Office Staff Manager
##TO RUN:
1. Install xampp
2. Go to phpmyadmin 
3. Create a database 
4. Run the tables.sql
5. Copy and paste the source code into xampp/htdocs
6. Edit config.ini to match database config
7. Go to "localhost" in browser to view web app
8. Initial log in is admin/admin

##FUNCTIONALITIES:
Three roles: admin, agent, staff
######Admins
1. Create user accounts for staff and agents
2. Set up schedules for agents
3. Delete user accounts and tasks
4. Reset passwords
5. Create tasks

######Agents
1. Create tasks
2. Monitor task completion

######General
1. Leave comments on tasks
2. Upload photos to tasks
3. Receive alerts when task is updated (status update, photo upload, creation, etc)
4. Staff can only log on during their scheduled time
5. Tasks are assigned on first available & whether their shift is ending soon
6. Staff & agents can only view tasks associated with them; thus, they can only receive alerts for those tasks
