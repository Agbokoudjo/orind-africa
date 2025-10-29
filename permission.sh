HTTPDUSER=$(ps aux | grep -E '(apache|www-data)' | grep -v 'grep' | head -n 1 | awk '{print $1}')
sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:pirate:rwX var
sudo setfacl -R -m o::rX var

sudo setfacl -R -d -m u:"$HTTPDUSER":rwX -m u:pirate:rwX var