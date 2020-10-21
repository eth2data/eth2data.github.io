cat << EOT > ~/prysm.service
[Unit]
Description=Prysm Beacon Node
[Service]
ExecStart=/bin/sh -c "/home/sid/prysm/prysm.sh beacon-chain --http-web3provider=/data/geth/geth.ipc --datadir=/data/prysm --log-file=/data/prysm.log --log-format=json"
ExecReload=/bin/kill -HUP \$MAINPID
KillMode=process
Restart=on-failure
RestartPreventExitStatus=255
Type=simple
LimitNOFILE=infinity
[Install]
WantedBy=multi-user.target
EOT

mv ~/prysm.service /etc/systemd/system/prysm.service

systemctl enable prysm
systemctl start prysm

