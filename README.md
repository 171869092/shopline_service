# tquq_apps #
包含:
  * redis:alpine
  * hyperf:alpine

# 怎么运行 #
先将 env.example 复制成 .env
选创建一个tquq_apps网络

docker network create -d bridge --subnet=179.20.0.0/16 --gateway=179.20.0.1 tquq_apps

cd到docker-compose根目录 直接运行 docker-compose up -d

# 怎么关闭 #
cd到docker-compose根目录  直接运行 docker-compose down

# 怎么重启 #
cd到docker-compose根目录  直接运行 docker-compose restart
重启注意需要删除 runtime/* 这个文件！因为在run的时候会生成代理类，如果不删除部分代码可能会不生效

# 启动后怎么监控进程 #
docker logs -f tquq_service
或者本地调试 直接把 docker-compose.yml 里面的 command:php bin/hyperf.php start 注释掉,在启动
然后 docker exec -it tquq_apps sh 进到容器后运行 php bin/bin/hyperf.php start 后就启动项目了

# 怎么关掉启动后 info级日志 #
在config/config.php 文件中注释掉 LogLevel::INFO