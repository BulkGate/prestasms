set -e

# get directory of this file
SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)

# get --build-arg from env file
docker build $(cat .docker/.env | xargs -I {} echo --build-arg {}) "$SCRIPT_DIR" -t prestasms

# run detached container
#CONTAINER_ID=$(docker run -d prestasms bash)

# copy container filesystem into host (IDE code completion purposes)
#docker cp $CONTAINER_ID:/var/www/html "$SCRIPT_DIR/../prestashop"

# stop container
#docker rm -f $CONTAINER_ID
