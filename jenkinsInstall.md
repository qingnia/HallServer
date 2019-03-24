### jenkins

```
#JDK
yum install -y java-1.8.0-openjdk java-1.8.0-openjdk-devel
#从http://mirrors.jenkins-ci.org/ 网址上找到适合的rpm包地址
rpm -ivh http://mirrors.jenkins-ci.org/redhat/jenkins-2.168-1.1.noarch.rpm
#启动
service jenkins start
#网页端打开对应的jenkins按照步骤初始化
http://118.89.27.78:8080
#初始密码在本地看
cat /var/lib/jenkins/secrets/initialAdminPassword
#先装推荐插件。。

php插件列表 http://jenkins-php.org/installation.html

```
##### php插件安装
```sh
#phive
#参考https://phar.io
wget -O phive.phar https://phar.io/releases/phive.phar
wget -O phive.phar.asc https://phar.io/releases/phive.phar.asc
gpg --keyserver pool.sks-keyservers.net --recv-keys 0x9D8A98B29B2D5D79
gpg --verify phive.phar.asc phive.phar
chmod +x phive.phar
sudo mv phive.phar /usr/local/bin/phive

#用phive安装phpab（php的测试工具）
#参考https://github.com/theseer/Autoload/#readme
phive install phpab
ln -s /home/qingniao/gitcode/phive/tools/phpab phpab

#安装phpunit
wget -O phpunit https://phar.phpunit.de/phpunit-8.phar
chmod +x phpunit
mv phpunit /usr/bin/
#测试phpunit要生成autoload.php，代码在官网，命令如下
phpab -o src/autoload.php -b src composer.json
#测试phpunit
phpunit --bootstrap src/autoload.php tests/EmailTest
phpunit --bootstrap src/autoload.php --testdox tests

#phploc
wget https://phar.phpunit.de/phploc.phar
chmod +x phploc.phar
sudo mv phploc.phar /usr/bin/phploc

#phpmd
wget -c http://static.phpmd.org/php/latest/phpmd.phar
chmod +x phpmd.phar
cd /usr/bin/
sudo ln -s /home/web/phptools/phpmd.phar /usr/bin/phpmd

#phpcpd
wget https://phar.phpunit.de/phpcpd.phar
chmod +x phpcpd.phar
sudo ln -s /home/web/phptools/phpcpd.phar /usr/bin/phpcpd

#phpdox
wget http://phpdox.de/releases/phpdox.phar
sudo ln -s /home/web/phptools/phpdox.phar /usr/bin/phpdox
```
##### phpab涉及到的composer.json要自己写，放在src外面就行,两个测试mail的php文件也都要有
```
{
    "autoload": {
        "classmap": [
            "src/"
        ]
    },
    "require-dev": {
        "phpunit/phpunit": "^8"
    }
}
```
##### jenkins支持php打包
```
参考文档http://jenkins-php.org/integration.html
#找到jenkins安装目录，我的是/var/lib/jenkins
locate jenkins
cd /var/lib/jenkins/jobs
#网上下载php的模板，然后给权限
mkdir php-template
cd php-template
wget https://raw.github.com/sebastianbergmann/php-jenkins-template/master/config.xml
cd ..
chown -R jenkins:jenkins php-template/
#网页端打开jenkins->系统管理->读取设置
#重启后就有php的模板了
#新建任务->填名称->拉到最下面《复制栏》填php-template
```
##### 执行构建
```sh
#项目配置示例：https://github.com/sebastianbergmann/money
#jenkins构建用的是Apache的ant，所以要做准备
#项目根目录要下载build.xml，默认代码路径是/path/src，如果原项目没有src的话，把src去掉就行
#下载build.xml
wget http://jenkins-php.org/download/build.xml
#去掉src: vim打开:%s/src//gc
#测试文件夹是tests，要手动创建这个文件夹
mkdir tests

#phpunit需要xdebug
yum install php-xdebug
yum install ant
#执行看报错，可以检查语法错误如：
ant
---
lint:
     [lint] No syntax errors detected in /home/web/bak/common_frame_helper.php
     [lint] No syntax errors detected in /home/web/common/action/gm.action.php
     [lint] Errors parsing /home/web/common/action/timeMgr.action.php
     [lint] PHP Parse error:  syntax error, unexpected 'dbAgent' (T_STRING) in /home/web/common/action/timeMgr.action.php on line 15
     [lint] Result: 255
     [lint] No syntax errors detected in /home/web/common/dao/dao.php
---
```
##### phpab报错处理，出错仔细看日志...
```
[qingniao@VM_53_170_centos web]$ phpab -o autoload.php -b . composer.json
phpab 1.25.3 - Copyright (C) 2009 - 2019 by Arne Blankerts and Contributors

Scanning directory /home/web/.

Multiple declarations of trait(s), interface(s) or class(es). Could not generate autoload map.

Unit 'log' defined in:
 - /home/web/common/utils/log.php
 - /home/web/common/utils/activity.php
```
### jenkins自动部署配置
```sh
#需要先安装publish over SSH插件
#后台->系统管理->节点管理->新建节点
#主要配置项：主机(ip)，credentials(ssh账号),verify策略(non)。其他项如主机名字等可以自由设置，路径自己定
#到后台允许jenkins登陆(将jenkins登陆设为/bin/bash，默认为/bin/false)
vim /etc/passwd
切换到jenkins登陆一下
```
