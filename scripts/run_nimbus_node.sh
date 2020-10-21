#!/bin/bash

set +e

trap "trap - SIGTERM && kill -- -$$" SIGINT SIGTERM EXIT

cd $(dirname "$0")
cd nim-beacon-chain

NETWORK=$1
NODE_NAME=${NODE_NAME:-$(whoami)}

if [[ "$2" == "" ]]; then
  NODE_ID=0
else
  NODE_ID=$2
  NODE_NAME=$NODE_NAME-$2
fi

let METRICS_PORT=8008+${NODE_ID}
let RPC_PORT=9190+${NODE_ID}

# add your node to eth2stats and run a data collector app that connects to your beacon chain client
mkdir -p /tmp/${NODE_NAME}
../eth2stats-client/eth2stats-client run \
  --data.folder=/tmp/${NODE_NAME} \
  --eth2stats.node-name="${NODE_NAME}" \
  --eth2stats.addr="grpc.${NETWORK}.eth2stats.io:443" --eth2stats.tls=true \
  --beacon.type="nimbus" \
  --beacon.addr="http://localhost:$RPC_PORT" \
  --beacon.metrics-addr="http://localhost:$METRICS_PORT/metrics" > /tmp/ethstats.$NODE_NAME.log 2>&1 &

# build and run the beacon node
make NIMFLAGS="-d:insecure" NODE_ID=$NODE_ID ${NETWORK}