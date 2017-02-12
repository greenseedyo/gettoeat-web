deploy:
	sudo rsync -av --exclude-from=rsync_exclude.txt ./ root@54.241.25.213:/home/www/verybuy.com.tw/buddyhouse/
	sudo rsync -av --exclude-from=rsync_exclude.txt ./ root@54.241.244.226:/home/www/verybuy.cc/buddyhouse/
	sudo rsync -av --exclude-from=rsync_exclude.txt ./ root@54.241.244.226:/home/www/verybuy.cc/bhtest/
