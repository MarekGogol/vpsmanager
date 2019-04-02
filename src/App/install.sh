echo '-------- Installation started! --------';

MANAGER_PATH=/etc/vpsmanager;
MANAGER_USER=vpsmanager_host

# Create user if does not exists
if getent passwd $MANAGER_USER > /dev/null 2>&1; then
    echo "> User $MANAGER_USER exists"
else
    useradd -s /bin/bash -d $MANAGER_PATH -U $MANAGER_USER
    echo "> User $MANAGER_USER created"
fi

# Check if directory exists, if not, create...
if [ ! -d "$MANAGER_PATH" ]; then
    mkdir $MANAGER_PATH
    echo "> Created $MANAGER_PATH"
fi

#Copy files
cp -R ./* $MANAGER_PATH
echo "> Copied data from package to $MANAGER_PATH"

# Changed owner of directory
chown -R vpsmanager_host:vpsmanager_host $MANAGER_PATH
echo "> Changed owner of $MANAGER_PATH to $MANAGER_USER"

# Change permissions of directory
chmod -R 700 $MANAGER_PATH
echo "> Changed permisions of $MANAGER_PATH to 700"


su - $MANAGER_USER -c "composer install -d $MANAGER_PATH"
# php vspmanager install command
# create user vpsmanager
# change privilegies to user
# open crontab of user and run command php vpsmanager migrate
# create domain nginx host with php open_basedir for /etc/vpsmanager