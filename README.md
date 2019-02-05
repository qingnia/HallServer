# HallServer
设想的开发效率更高的大厅服务器

### php7开发环境
```sh
#epel源
rpm -vih http://dl.fedoraproject.org/pub/epel/7/x86_64/Packages/e/epel-release-7-11.noarch.rpm
#remi源
rpm -Uvh http://rpms.remirepo.net/enterprise/remi-release-7.rpm
#或者是下载：
wget http://rpms.remirepo.net/enterprise/remi.repo
wget http://rpms.remirepo.net/enterprise/remi-safe.repo

#可以不执行，估计是重新加载yum缓存
yum clean all && yum makecache

yum-config-manager --enable remi-php73
yum -y install php php-opcache
#常用扩展
yum -y install php-mysql php-gd php-ldap php-odbc php-pear php-xml php-xmlrpc php-mbstring php-soap curl curl-devel php-apc php-redis
#nginx
yum -y install nginx nginx-mod-http-perl nginx-mod-stream nginx-filesystem nginx-mod-mail nginx-mod-http-image-filter nginx-all-modules nginx-mod-http-geoip nginx-mod-http-xslt-filter

yum install php-fpm
systemctl start php-fpm.service

#修改nginx，对.php重定向
    location ~ \.php$ {
    fastcgi_pass   127.0.0.1:9000;
    fastcgi_index  index.php;
    # 文件位置修改为/usr/share/nginx/html
    #fastcgi_param  SCRIPT_FILENAME /usr/share/nginx/html$fastcgi_script_name;
    fastcgi_param  SCRIPT_FILENAME  /home/web$fastcgi_script_name;
    include        fastcgi_params;
    }

-----忽略设置
#重定向php错误日志
vim /etc/php-fpm.d/www.conf
php_admin_value[error_log] = /home/web/log/php-fpm/www-error.log
systemctl restart php-fpm.service

# 将php错误输出到nginx
#1.修改/etc/php.ini:
display_errors = On
#2.修改/etc/php-fpm.d/www.conf
php_flag[display_errors] = on
-----

#php的protobuf扩展
yum install -y php-pear php73-php-devel php-devel autoconf automake libtool make gcc
pecl install protobuf-3.6.1

#php的protobuf转换程序
google下载prot.php.tar.gz
解压、
./configure
make
make install
```

##### 数据库建表
```
create table `account_tb` (`roleID` bigint(20) auto_increment primary key, `openID` varchar(32) not null, `ditch` int(8) default 0, `create_ts` bigint(20) not null, `update_ts` bigint(20) not null, `name` varchar(64) not null, `gold` bigint(20) default 0, `diamonds` bigint(20) default 0, key `open`(`openID`, `ditch`));
```
