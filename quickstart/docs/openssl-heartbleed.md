

- the bug: http://heartbleed.com/
- test tool: https://www.ssllabs.com/ssltest/

# fixed on our server:

our server using centos as the operating system, so, do:

```
yum update
yum upgrade openssl
```

will finish the upgrade, after upgrading, the version of
open ssl is "openssl-1.0.1e-16.el6_5.7.x86_64", and the server
is not vulnerable to the Heartbleed attack now.