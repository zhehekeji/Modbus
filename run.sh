#!/bin/bash

PWD=$(cd `dirname $0`;pwd);

REG_SERVER='121.199.53.9'
REG_SERVER_PORT='5000'
IMG_NAME="$REG_SERVER:$REG_SERVER_PORT/cems-neizu"
IMG_VER='2.0.0'
PORT=7100
DATA_PORT=8582
UPGRADE_PORT=8584
MODBUS_PORT=8586
NAME='cems-neizu'

if [ $# -gt 1 ];then
    NAME=$2
fi

if [ $# -gt 2 ];then
    PORT=$3
fi

if [ $# -gt 3 ];then
    DATA_PORT=$4
fi

run() {
    echo "docker run --restart=always --name $NAME -d -p$PORT:80 -p$DATA_PORT:8282 -p$UPGRADE_PORT:8283 -p$MODBUS_PORT:8284 -v ${PWD}/www:/var/www -v ${PWD}/sites:/etc/nginx/conf.d -v ${PWD}/logs:/var/log/supervisor ${IMG_NAME}:${IMG_VER}"
    docker run --restart=on-failure:10 --name $NAME -d -p$PORT:80 -p$DATA_PORT:8282 -p$UPGRADE_PORT:8283 -p$MODBUS_PORT:8284 -v ${PWD}/www:/var/www -v ${PWD}/sites:/etc/nginx/conf.d -v ${PWD}/logs:/var/log/supervisor ${IMG_NAME}:${IMG_VER}
}

case $1 in
    build)
        echo "docker build -t ${IMG_NAME}:${IMG_VER} ."
        docker build -t ${IMG_NAME}:${IMG_VER} .
        exit $?
        ;;
    start)
        docker start $NAME
        exit $?
        ;;
    stop)
        docker exec -it $NAME /bin/bash /var/www/bin/rm_pid.sh
        docker stop $NAME
        exit $?
        ;;
    rm)
        docker rm $NAME
        exit $?
        ;;
    run)
        run
        exit $?
        ;;
    clear)
        docker exec -it $NAME /bin/bash /var/www/bin/rm_pid.sh
        exit $?
        ;;
    *)
        echo "Usage: run.sh {build|start|stop|rm|run|clear} name port data_port." >&2
        echo "name and port is optional." >&2
        exit 1
        ;;
esac
