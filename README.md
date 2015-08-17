# ecshop_alipay_wap
目前ecshop的版本里面手机wap支付是个很老的版本，偶尔在余额宝支付的时候报错，所以自己重新封装了一套

之前ecshop里面手机浏览器页面采用的是老的wap支付网关，在实际使用的时候发现用户在使用支付宝里面网银

或者支付宝里余额宝的时候失败率很高，表现在支付宝支付成功了，但是网站（自己的）没有更改状态，痛定思痛

决定封装新的接口

==============
使用方法：

将ecshop_mobile路径下的文件覆盖复制到echsop的mobile路径下，然后在./plugins/payment/alipay_wap.php

文件里面的商家合作ID换上，./plugins/payment/keys/下面的密钥文件需要替换，只替换rsa_private_key.pem

这个文件就可以了（吐槽，支付宝原SDK里面的注释写的到处是错误，大家有其他问题可以私信我）
