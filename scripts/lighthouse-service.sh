cat << EOT > ~/lighthouse.service
[Unit]
Description=Lighthouse Beacon Node
[Service]
ExecStart=/bin/sh -c "/home/sid/lighthouse/target/release/lighthouse --testnet medalla beacon --staking --eth1-endpoint http://localhost:8545 --datadir /data/lighthouse/"
ExecReload=/bin/kill -HUP \$MAINPID
KillMode=process
Restart=on-failure
RestartPreventExitStatus=255
Type=simple
LimitNOFILE=infinity
[Install]
WantedBy=multi-user.target
EOT

mv ~/lighthouse.service /etc/systemd/system/lighthouse.service

systemctl enable lighthouse
systemctl start lighthouse

