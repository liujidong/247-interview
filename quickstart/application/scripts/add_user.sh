if [ $# -ne 2 ]
then 
    echo "Expected Params: <user name> <public key>"
    exit 1
fi

username=$1
pubkey=$2

echo "Add ${username}"
sudo useradd $username
echo "Add ${username} to root"
echo "${username} ALL = NOPASSWD: ALL"|sudo tee -a /etc/sudoers>/dev/null

echo "Create .ssh folder"
sudo su - $username -c "mkdir -p /home/${username}/.ssh"
echo "Set access mode of .ssh to 700"
sudo su - $username -c "chmod 700 /home/${username}/.ssh"
echo "Write the public key to authorized_keys"
sudo su - $username -c "echo ${pubkey}>/home/${username}/.ssh/authorized_keys"
echo "Set access mode of authorized_keys to 600"
sudo su - $username -c "chmod 600 /home/${username}/.ssh/authorized_keys"
echo "user ${username} created successfully!"