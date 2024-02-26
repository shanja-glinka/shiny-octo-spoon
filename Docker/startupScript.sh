CONTAINER_ALREADY_STARTED="CONTAINER_ALREADY_STARTED_PLACEHOLDER"
if [ ! -e $CONTAINER_ALREADY_STARTED ]; then
	touch $CONTAINER_ALREADY_STARTED
	chmod -R 777 /var/www/app/uploads
	echo "-- First container startup --"
	cd /var/www/app/db
	# mysql -root -proot -hdb-host shiny_octo_spoon < shiny_octo_spoon.sql
	cd ..
	composer install --no-interaction
else
    echo "-- Not first container startup --"
fi