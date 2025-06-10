set -e

save_version_into_file() {
    local versions_file="$SCRIPT_DIR/versions.json"
    local php_version="$1"
    local prestashop_version="$2"

    php -r '
        $file = $argv[1];
        $php = $argv[2];
        $ps = $argv[3];
        $arr = json_decode(file_get_contents($file), true);
        $arr[] = ["php" => $php, "prestashop" => $ps];
        // Odstraň duplicity
        $arr = array_map("unserialize", array_unique(array_map("serialize", $arr)));
        // Seřaď podle prestashop, pak php
        usort($arr, function($a, $b) {
            $cmp = version_compare($a["prestashop"], $b["prestashop"]);
            if ($cmp !== 0) return $cmp;
            return version_compare($a["php"], $b["php"]);
        });
        file_put_contents($file, json_encode($arr, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    ' "$versions_file" "$php_version" "$prestashop_version"
}

# get directory of this file
SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)

# Zpracování argumentů
PUSH=1
EMIT_VERSION=1
PHP_VERSION=""
PRESTASHOP_VERSION=""
BUILD_ARGS=""

while [[ $# -gt 0 ]]; do
    case "$1" in
        --no-push)
            PUSH=0
            shift
            ;;
        --no-emit-version)
            EMIT_VERSION=0
            shift
            ;;
        --php-version)
            PHP_VERSION="$2"
            shift 2
            ;;
        --prestashop-version)
            PRESTASHOP_VERSION="$2"
            shift 2
            ;;
        *)
            shift
            ;;
    esac
done


if [[ -n "$PHP_VERSION" && -n "$PRESTASHOP_VERSION" ]]; then
    BUILD_ARGS="$BUILD_ARGS --build-arg PHP_VERSION=$PHP_VERSION --build-arg PRESTASHOP_VERSION=$PRESTASHOP_VERSION"
fi

# build image
docker build $BUILD_ARGS "$SCRIPT_DIR" -t ghcr.io/bulkgate/prestasms
VERSION=$(docker inspect --format '{{ .Config.Labels.version }}' ghcr.io/bulkgate/prestasms)

# tag image
docker image tag ghcr.io/bulkgate/prestasms ghcr.io/bulkgate/prestasms:$VERSION

# add version matrix
if [[ -n "$BUILD_ARGS" && $EMIT_VERSION -eq 1 ]]; then
    echo "writing version into file ... $VERSION"
    save_version_into_file $PHP_VERSION $PRESTASHOP_VERSION
fi

# push image
if [ $PUSH -eq 1 ]; then
    echo "Pushing image..."
    docker push ghcr.io/bulkgate/prestasms:$VERSION
fi
