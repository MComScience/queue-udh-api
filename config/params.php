<?php
$privateKey = <<<EOD
-----BEGIN RSA PRIVATE KEY-----
MIIEoQIBAAKCAQEAsoRSe0jKBDDVHg2tSZFkiPEz0QjKtuspl1481WvVdzJFGMIy
gIKtVRzylV4iCMU6qDbHxjBPQG/LvQLPwK49WxJLj1ADYZlXpgvyJSmsIMMnYU47
oqp4rftsAEXDVFn4F69GFiBy62sZb5FklCjAqY/VZ6ZqtK/pcUir+sY9jS5ZHIGA
XoIuDPOd39jbnZ3kkreJJQmhTEmlc39RCDWMyIWR65Kma5ni0nn4oeEum5eUGdp3
mR+mEk8T5W6XLJUf98ZTlru8Ltogk2KYbf537e569W/mhXOb7GNFMqhGkOeQ6RHj
5HJHCr7qafbHtdUsuG2uWHzfUTz7qqoOCkcivwIBJQKCAQBlUgVM5B+hg3/gn/qR
id8dSqDebMYUyqjnNXx5IYb+dno+fA7TURZFEG4Bv84gp0rOLO3tBqmFb+JWhQ4h
QExWSKdtA+Y+UBz9YLndvbS4ph1FELoQNz2TLdWDnTeC5vSX0i6ynMuaX2F2rHdb
Al+C1RhBv1FRy6ArihxrvJ95o7terHkI7TAnzbJoy34uUQOp9nAvUHKv1CMCgEqI
fR9I3pXSHmD84gjdwbRPvxcRKHQd/912JXNZKPtWBZ0JNbZ5tqeFeN57XIgPLJan
nAE/yQMQHjJxX4YgRZsLFnfZZcYflq88q5flqjpl3PIE7YCZa/VeNRYJUVJDZh1T
CdUVAoGBAOrxmTNr1PI2yBKbKDHI/6QbIG7DRaQD6HK6IO3AP4imAaQ7ODy8udiX
NqOG7DMAQsZCz1+MpB4J33XaxQCSgtM7rY+aMBpVmEouVlQXefUU5tbm/dghUkGV
299TzYHYx3h2Kp4lqcLifX5YyYIO7THN9i3UfP7rCjWQi6tL5vG1AoGBAMKEGfGZ
zmDlX5oSJY6PNBVwtCP2BXxbuqvluU2s0Gt9jgpKVaZrOWhTBmy/NpYDMhmiL+cw
ISqDubJzGZpM5GzBE+X4/9C2w2CTdJA7ZXR01j0izyM62pKXktrLUahp2n5sAloj
IEfEXqOm2L/O/GF6oOiU30vb4HIdReXvHhsjAoGAWOXLQ+OcrqzxwdnX23x8Z5uP
uzUhRPqPTgE85FaUlJHXG4wHcO12wKfrKR5LiOtXiUm2P9RZxi0/y7qPuwcBEafg
zo1eTydOYUH3JrzGXLvM915SNhpyJqdn86MrKjZZUCy2LgBpv1yeL86Rb3tE/haU
gAs2NvEmdSHvqJlCoKUCgYBZX0owvEoQ2BcrHRgsvlYzXUvZK9kByUfuHXDQqVjX
cQnpDWWffV+QzDNiZbFL1/RspHbf19fjGe2JV3p3U8Ll3Cu8sKzjWuQldC6jIjWI
iLV2CRMQL8w1g+mHHyWEuwOGMaA3QJJKfNGJdiw8c9u+Fb+NdNVMXhsD/5yn6mfH
RwKBgQDFNhatyCS6Q39vAoFFTblIkd4izOHJgBdxpzfz2Nmzcni9NENVX+RCrCcp
2FpeCuXZ/RUyAAFQT14/2wGxLROB9P+W6EJjsMjzF3X6he9ZlPf8yy+0vVpoLAln
HOKtjnCtmAj42wusiZ5hNzePXjjBmFGL8v89Iiw0Mk98bL2I4g==
-----END RSA PRIVATE KEY-----
EOD;
return [
    'adminEmail' => 'admin@example.com',
    'jwtSecretCode' => $privateKey,
    'icon-framework' => \kartik\icons\Icon::FA,
    'API_TOKEN' => '6615e94372943853d7dad7a3d847440e',
    'API_BASE_URL' => 'http://172.16.254.64:8081/api'
];
