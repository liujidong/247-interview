#!/bin/bash

DOCROOT="/var/www/html/pincommerce"

cd /root/
mkdir -p /root/.ssh
chmod 700 .ssh
cd /root/.ssh
cat > /root/.ssh/id_dsa<<EOF
-----BEGIN DSA PRIVATE KEY-----
MIIBvAIBAAKBgQDpL3jL7y7dNV/NNYgBYjHpo9T+isM7KPRZ/nrHYBFauT54+Gkg
SV3VkG4ly5UeAsTdpbsf2uAJwTkV1QOuwcSG6Tsl5QOB9ormKgIaINkorCx/ajcq
CegQOcGEEI887g4osBtcljdKHfcUL647JauOzci8YB26LwhkuUiD1Rz1mwIVAJoI
HDhUZRdxB23YIdGsyPsSgMb7AoGBALqhjra6p+h1eu/tNrW5y52N0x2X1uHgaRkg
5JwfKCxE6Uy0JCv21ohM9Mt2e/FLc4DqsS8GXIrmwzgWTB1iFRJNoJVbgkTDd6qW
KstdKF3qR93hFx9o9dgAiP3gK67zCbgoMe9rGDKVnHvr8MyFoldVYTrEyGE7pmD0
FDvyU51HAoGBALpFOW+Q+zWIhS/ulr1/vrSUs2Mi10BIiSCz2IY9RESbKoh3fnp2
IwLNq1LjtsYUaZFrpWfpSZfUGExVCGpoCG5Ki4Dxu8ZuCk86gvX+LeoT7RJy1kKx
f+42aLZgDUFa27+Tt7TQB9lNOvf3pyoDLBgfwKw45LIW2Tj8U8BKWDoVAhR8h7Cu
fJXatGiRCYs/2oNJ3NQRfg==
-----END DSA PRIVATE KEY-----
EOF
chmod 600 id_dsa

mkdir -p $DOCROOT 
cd $DOCROOT
yum install git 
git init 
git pull git@github.com:liangdev/pincommerce.git
git remote add origin git@github.com:liangdev/pincommerce.git

rpm -ivh http://yum.puppetlabs.com/el/6/products/i386/puppetlabs-release-6-6.noarch.rpm
yum install puppet
yum --disableexcludes=main install kernel-headers-2.6.32-279.14.1.el6.openlogic.x86_64
yum install gcc

exit 0



