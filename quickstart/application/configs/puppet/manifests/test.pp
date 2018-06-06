/*
import "utils"

create_user {'wyixin':
    user_name => 'wyixin',
    key => 'AAAAB3NzaC1yc2EAAAADAQABAAAAgQC73658UnlAggEI/l2e3waLYYMvShG9Dttq2GQzu9Ge64SVXsz6cvkFD8Pp8DNIgCbA7jnWNMlor+mc05Gjo/9fDOpHKDLg6PpBrxgmB2RUz4KxUyhBvaoRfpIjamRUGJQSatpdHddK5u0j5UE27SwAN6JsG63+YQN1Qe4KXTK8GQ==',
}

create_git_key { 'wyixin':
    user_name => 'wyixin',
    key => '-----BEGIN RSA PRIVATE KEY-----
MIIEoQIBAAKCAQEAphUa3LcMn84Z208pAhiN/2zJU0hJ/4A4Jmb/yZTCfgKwwC8p
jZnuSRmsUlH8HDtvhP4wBU3JSNo1xdcbnGhxE1aHsCRSC8Waimrihx/O+qPpRuU1
B+jgoTbhXdTj1OUqBn31SeQfYszmh6sQrgbQuzg1EuTONHorKPg8FEowHMP0XvXl
TYSSOL0GMpHWmL0kvAyWTVMg4EINvoHvLiFd5wwWX/SSvnhCNHm6Vu5O37y2Gdo7
bJ2tee4zwf1tKJVcnTo5QelYPOT/t0UK5YjjYj+FMzhcQ1Z9M6Qts9/I0J8fC4L8
p/yVghUJyMPQLxZ4OdKEjWzIoTlpami/kjbc2QIBIwKCAQBL7GQNIHrMtf0xDj6h
31bbKmqpuqV8HVt/RQc3lHYq+epme/W9EysaGl1nde+X4KgCSFB3dAQ+j6ONpCnh
Gc1Kqza27Ag/5U32IjurfEFcorPIo0txVIPx7TPTH34YLj8Y6R+swA5ZDTYvZCTh
1zrZPkQl5PCNBKYEGbUQlvFsO5Xrm9hAkdLl2w2gFZT4FMl5RX+YLTA45WtCxQxU
vJLGM3prbXGDTdl1HBqeiM7p9YpIR4hjrRpfWeK5kfkLrPjxy1k3bOrNQBsCYdrE
9zlN3wQN5elf2eg6OxBUOU4+mzzaqkYllbAkg6TL5aoube49jDUVx/hPPq7d/vsT
s+6LAoGBANCN9y8QVSrtdtlgl3LBHCKpyHGRMvhq0g3d4X+gRkQ1HlEdRyFINOsh
CQ3pXRRH+Il3r1tTWP4XBGLRe8JbvFsN6d2PDtKH5nzxImhve+PvjPOg7dZ8usKc
NBKAH4PDmBBhL3EOU3AR17iuYhAC+ROqYAi/M8lMiCotbpm3JQhtAoGBAMvdmc0v
8Gn0dv8Dyy3484lY/B2fPU74+iU+kWSUfpigXSl3rgknCQtjQzIyxfX3+w4kDmW/
OgWyVTD6kloDTqD+Y4iJnEQUAkew7JPnMDIYWriz45dE7w6YDud7gunkE3mL/xIn
cbQgXVicW1vmjOtoz8euk5AnWCC3XGCf+DqdAoGBAL6trsvxrPQMT2eoxPs0Nvse
4yYBGKicLcOJCK8zc3GPprCPzAEsEyAeNCn57rN1AH2vQT2OCDjElku4NqMSC0vv
drvwgpSZgkZYzwBl7Z0yygNRTnr1shED19ZmgzakM0Is+CzZ5eLOfBaQzq+NsIcC
K+q9cS0SxaLntZPapYQLAoGAYwU8EzSKtx73+DUK6nGa3E/K6dEAhXGeEhcTa14R
mpcIrb3Jj2q7QAukS5UBERIMOhGDVf3LuZ/C+ohVtrEmMPCWvqk9RaNRkIkic7lv
LkZX81APdVv/FbeSNeuIur88qMBXUfXmxTRKmMD5LKMugPhk6/W1Y0ZPYFkPnKV4
kX8CgYAa8A2gQ2QXj5o1eQO7yE2o06W4qXN7h5h/Lr+QvF8Eev2c6ln/ITiQaAgo
G03WbUDXr23KeRIwQNZLXjb/H/uIgxQjgyrmPAamzaZiHskYob57+Y1GDiC5wewP
het0dTniRhNOwSFMg6hY+S6S5ipUOiWova91WZx8ADikAqHSFw==
-----END RSA PRIVATE KEY-----',
}

install_git {'git':

}

$package = ['git', 'mysql', 'php', 'php-mysql']
install_package { $package:
}


install_package { 'httpd':
}

install_package { 'mysql-server':
}

$service = [mysqld, httpd]
run_service { $service:
}


pecl_install_http { 'install-pecl-http':}

class my_class {
  notify {"This actually did something":}
}

class { "redis":
    source => [ "puppet:///modules/lab42/redis/redis.conf-${hostname}" , "puppet:///modules/lab42/redis/redis.conf" ], 
}
include redis
*/
