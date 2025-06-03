set -e

# Load versions from JSON and emit --build-arg parameters
build_args_from_versions() {
    local versions_file="$1"
    php -r '
        $versions = json_decode(file_get_contents($argv[1]), true);
        if (!$versions) { exit(1); }
        foreach ($versions as $k => $val) {
            printf("--build-arg %s=%s ", strtoupper($k) . "_VERSION", $val);
        }
    ' "$versions_file"
}

# get directory of this file
SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)

# Zpracování argumentů
PUSH=1

while [[ $# -gt 0 ]]; do
    case "$1" in
        --no-push)
            PUSH=0
            shift
            ;;
        *)
            shift
            ;;
    esac
done

# build image
docker build $(build_args_from_versions "$SCRIPT_DIR/versions.json") "$SCRIPT_DIR" -t ghcr.io/bulkgate/prestasms
VERSION=$(docker inspect --format '{{ .Config.Labels.version }}' ghcr.io/bulkgate/prestasms)

# tag image
docker image tag ghcr.io/bulkgate/prestasms ghcr.io/bulkgate/prestasms:$VERSION

# push image
if [ $PUSH -eq 1 ]; then
    echo "Pushing image..."
    docker push ghcr.io/bulkgate/prestasms:$VERSION
fi
