echo '-------- Installation started! --------';

sudo apt-get update

MANAGER_PATH=/etc/vpsmanager;
MANAGER_USER=vpsmanager_host

# Check if nginx is installed
dpkg -s nginx &> /dev/null
IS_NGINX=$?
if [ $IS_NGINX -eq 0 ]; then
    echo "Nginx is installed."
else
    echo "Nginx is not installed"

    read -p "Do you want to install Nginx? [Y/n]: " answer
    answer=${answer:Y}

    if [[ $answer =~ [Yy] ]]; then
        apt install -y nginx
    fi
fi

# Check if php-cli is installed
dpkg -s php7.1-cli &> /dev/null
PHP71=$?
dpkg -s php7.2-cli &> /dev/null
PHP72=$?
dpkg -s php7.3-cli &> /dev/null
PHP73=$?
if [ $PHP71 -eq 0 ] || [ $PHP72 -eq 0 ] || [ $PHP73 -eq 0 ]; then
    echo "Minimum PHP verion installed."
else
    echo "Minimum PHP for installation is not installed"

    read -p "Do you want to install PHP (7.1, 7.2, 7.3)? [Y/n]: " answer
    answer=${answer:Y}

    if [[ $answer =~ [Yy] ]]; then
        apt install -y software-properties-common
        add-apt-repository -y ppa:ondrej/php
        # apt install -y php7.1-fpm && apt install -y php7.1-cli php7.1-fpm php7.1-json php7.1-pdo php7.1-mysql php7.1-zip php7.1-gd php7.1-mbstring php7.1-curl php7.1-xml php7.1-bcmath php7.1-json
        apt install -y php7.2-fpm && apt install -y php7.2-cli php7.2-fpm php7.2-json php7.2-pdo php7.2-mysql php7.2-zip php7.2-gd php7.2-mbstring php7.2-curl php7.2-xml php7.2-bcmath php7.2-json
        # apt install -y php7.3-fpm && apt install -y php7.3-cli php7.3-fpm php7.3-json php7.3-pdo php7.3-mysql php7.3-zip php7.3-gd php7.3-mbstring php7.3-curl php7.3-xml php7.3-bcmath php7.3-json
    fi
fi

# Check if mysql is installed
dpkg -s mysql-server &> /dev/null
IS_MYSQL=$?
if [ $IS_MYSQL -eq 0 ]; then
    echo "MySQL is installed."
else
    echo "MySQL is not installed"

    read -p "Do you want to install MySQL? [Y/n]: " answer
    answer=${answer:Y}

    if [[ $answer =~ [Yy] ]]; then
        apt install mysql-server
        mysql_secure_installation
    fi
fi

# Check if composer is installed
dpkg -s composer &> /dev/null
IS_COMPOSER=$?
if [ $IS_COMPOSER -eq 0 ]; then
    echo "Composer is installed."
else
    echo "Composer is not installed"

    read -p "Do you want to install Composer? [Y/n]: " answer
    answer=${answer:Y}

    if [[ $answer =~ [Yy] ]]; then
        apt install -y composer
    fi
fi

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
php $MANAGER_PATH/vpsmanager install --vpsmanager_path=`pwd`
# create user vpsmanager
# change privilegies to user
# create domain nginx host with php open_basedir for /etc/vpsmanager