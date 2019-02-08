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
yum install -y php-pear php-bcmatch php73-php-devel php-devel autoconf automake libtool make gcc
pecl install protobuf stable
#修改php.ini，添加：
extension=protobuf.so

#php的protobuf转换程序
google下载prot.php.tar.gz
解压、
./configure
make
make install
#将proto协议转换为php文件
protoc --php_out=/home/web/msg/ msgDef.proto
```

##### 数据库建表
```
#主库
create table `account_tb` (`roleID` bigint(20) auto_increment primary key, `openID` varchar(32) not null, `ditch` int(8) default 0, `create_ts` bigint(20) not null, `update_ts` bigint(20) not null, `name` varchar(64) not null, `gold` bigint(20) default 0, `diamonds` bigint(20) default 0, key `open`(`openID`, `ditch`));

#log
create database log;
use log;
create table `money_log` ( `roleID` bigint(20) not null, `reason` int(8) not null, `param1` int(8) not null, `param2` varchar(1024) default '', `param3` varchar(1024) default '', `param4` varchar(32) default '', `param5` varchar(32) default '', `ts` bigint(20) not null primary key, key `role` (roleID), key `reason` (reason) ) ENGINE=InnoDB PARTITION BY range (ts) (partition p0 values less than (1551369600) engine = InnoDB, partition pother values less than (MAXVALUE) ENGINE = InnoDB);
create table `event_log` ( `roleID` bigint(20) not null, `reason` int(8) not null, `param1` int(8) not null, `param2` varchar(1024) default '', `param3` varchar(1024) default '', `param4` varchar(32) default '', `param5` varchar(32) default '', `ts` bigint(20) not null primary key, key `role` (roleID), key `reason` (reason) ) ENGINE=InnoDB PARTITION BY range (ts) (partition p0 values less than (1551369600) engine = InnoDB, partition pother values less than (MAXVALUE) ENGINE = InnoDB);
```
##### 本地redis服务设置
```sh
wget http://download.redis.io/redis-stable.tar.gz
tar xvfz redis-stable.tar.gz
cd redis-stable/
make
make install
#设置连接密码
vim redus.conf
#添加一行：
requirepass 123456
#将redis-cli命令设置成全局
cp src/redis-cli /usr/bin/

#安装screen，将redis-server后台启动
yum install screen
screen -S "redis"
src/redis-server redis.conf
#叉掉当前屏幕

#测试
redis-cli -h 127.0.0.1 -a 123456
```
