cat << EOT > ~/geth.service
[Unit]
Description=Geth Node
[Service]
ExecStart=/bin/sh -c "/usr/bin/geth --ws --gcmode archive --datadir=/data/geth --goerli --http --verbosity 3"
ExecReload=/bin/kill -HUP \$MAINPID
KillMode=process
Restart=on-failure
RestartPreventExitStatus=255
Type=simple
LimitNOFILE=infinity
[Install]
WantedBy=multi-user.target
EOT

mv ~/geth.service /etc/systemd/system/geth.service

systemctl enable geth
systemctl start geth

