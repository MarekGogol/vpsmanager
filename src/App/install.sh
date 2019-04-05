MANAGER_PATH=/etc/vpsmanager;
MANAGER_USER=vpsmanager_host

# Install server requirements
git clone https://github.com/MarekGogol/vps-wamp-setup /root/vps-wamp-setup
bash /root/vps-wamp-setup/setup.sh

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

#Copy package files files
if [ -d "vendor/marekgogol/vpsmanager/src/App" ]; then
    cp -R ./vendor/marekgogol/vpsmanager/src/App/* $MANAGER_PATH
    echo "> Copied data package from vendor/marekgogol/vpsmanager/src/App/* to $MANAGER_PATH"
elif [ -f "./install.sh" ]; then
    cp -R ./* $MANAGER_PATH
    echo "> Copied data package from ./ to $MANAGER_PATH"
else
    echo "> Could not find APP files"
    exit
fi


# Change permissions/owner of directory
chmod -R 700 $MANAGER_PATH
chown -R $MANAGER_USER:$MANAGER_USER $MANAGER_PATH
echo "> Changed owner of $MANAGER_PATH to $MANAGER_USER"
echo "> Changed permisions of $MANAGER_PATH to 700"

# Install composer vendor files
su - $MANAGER_USER -c "composer install -d $MANAGER_PATH"

# Start installation process
php $MANAGER_PATH/vpsmanager install --vpsmanager_path=`pwd` --dev=1 --host=docker.marekgogol.sk --open_basedir=/volumes --no_chmod=1
# create user vpsmanager
# change privilegies to user
# create domain nginx host with php open_basedir for /etc/vpsmanager