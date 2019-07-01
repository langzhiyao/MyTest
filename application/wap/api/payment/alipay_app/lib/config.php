<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2018101261632999",

		//商户私钥，您的原始格式RSA私钥
		'merchant_private_key' => "MIIEpAIBAAKCAQEAs+5lFbMiDq+8b7WxxO662HbQO2zTZwuNo9r+/D6RAAZ/MdSCIRkfqNviqq5BPkxRXD47qdL0oHmtN8KP0IZfh6iyio92rugbWdHD2nsqYBbM5AbBIKMGKGXC+H/Sxpnz04U5lIFLwYT4Uzt0fYuMga9mOwOVxK3+lLTpzhfUQv7VhbYZAXbd4DGMjDu4vZOdyQFF1+Jo5yQHmwAdoO2YvmLbkb0TbfGETHsYYZ8uFGxrn78Yt8KWfG5TjDDq+Gi6WNdaubGe4VthJsDK0TLNVrTrGVJrwUfyqzFWoVRWDc+fo4lScB1Pazfpt3S2bO9drrUeAIF0uLPkN1/9nG8e7QIDAQABAoIBAQCW8cAlYyCIF49KW6+lWOywWYQ4xgPXJ08MjdRpQgeccNbVs8PzBkOUAdr2erbLD3UVoDOnNQz5bvoMlBDXy1Jq2O/m45GaC6eeQyY9rCORdq8uACiWO40X6L6Wf40QBOkSomn6ljk6QFWeZXtFMwONa9qkWU6UL68pi1e1CDKOfBgD8YEHYW/JXGlMeXQtQFvi04EdSz8/T7H5yrjHIgHPQuEDqVtfmpY2dGFwl5wvLBFy/yIqs9HAnQPykjGqbWyhR8O5ASpiDVBDwfCYVnBq2e65xWOLiHzNWtrboTdB6+LaV0niJ0KGtAKQzEfigk0ykdMPDxcz4RBuXpXaiod1AoGBANfP1fKhF7F9lAmG4+iShaqT0GPQOuPVROpKEKege9uVZAObp+MiHz0BoKooC5Y8Z+zIGYgxB8LsFt+kpAF6AVxWXd1CGLw/vFXRD/TMCxWQAa83Clwz+u/S1k/9+MsHFJ/wv7vHlUWWr34/UJLOOh4N7m7A7y2J942uW7S3QCurAoGBANVwD5fjivqy0v3DGBCnrIB5oLWfnJ8ZyGxEOZw4tSh38LN4x4VhoIzAJd4/MkzgxmHDaYxmy/prOPS0eX1NG1ttQZYWE2CbbgViNL0bhiKh/iICliaFn1VWWRQe3cDGChxdxruu8BXfIzMLfJBvPsdxu7FVvBv3pT7xu5NBkofHAoGBAI9GLt6+rhEqsr5EbEg6Bj2Bloa9dmtNakw00avHspLMMjLnAuWbAKwqyXRN06tw/PnIupKdIm74+BKPpkZIrmCUAgrjV7Kaiblba0F0uSMekM+3NcySwdS334oJrUN0tu+cEF5lGSey611gQWjFOvY6/4FN2zwbbVsFV2K5igOzAoGATK4i3AXCs4i1ZrfFmI402j9YLog2Y66Go+bq76Yr52sXzdKxk8jcPlT2Bd+ceaQzgYIMFIYF+GS3JKsGDq86CeG5s2sg8Q9GOqbqnZoa2axrOdrGCPbupGEORb1FG/HKRiCmul0CIkl1Qux4hweAfXTt4qsTltSkyHVeV2wycZcCgYBVHFDX6SuPr6p/DIxU2NIjzn/WNRmSj8T3q+xjtD8lmXieWoHmL6vACSKPi7Y51gOCJLG5neUnMSCngBNwFqTcl3oWlBBz0elNcMaMmBW2xk2yHqqKouzHdU0MXdZyvP8mX5FBYacs3h8kOmzcCmzOVENtCbS9Oap+wWK0gudxBg==",
		
		//异步通知地址
		'notify_url' => "http://".$_SERVER['HTTP_HOST']."/index.php/Wap/payment/alipay_notify_app.html",
		
		//同步跳转
		'return_url' => "http://".$_SERVER['HTTP_HOST']."/app/user/orderform.html",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//商户应用公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAs+5lFbMiDq+8b7WxxO662HbQO2zTZwuNo9r+/D6RAAZ/MdSCIRkfqNviqq5BPkxRXD47qdL0oHmtN8KP0IZfh6iyio92rugbWdHD2nsqYBbM5AbBIKMGKGXC+H/Sxpnz04U5lIFLwYT4Uzt0fYuMga9mOwOVxK3+lLTpzhfUQv7VhbYZAXbd4DGMjDu4vZOdyQFF1+Jo5yQHmwAdoO2YvmLbkb0TbfGETHsYYZ8uFGxrn78Yt8KWfG5TjDDq+Gi6WNdaubGe4VthJsDK0TLNVrTrGVJrwUfyqzFWoVRWDc+fo4lScB1Pazfpt3S2bO9drrUeAIF0uLPkN1/9nG8e7QIDAQAB",

		//支付宝应用公钥
		'merchant_public_key' =>"MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAlPXfE+mKzBC+NBgN68OORr2WtqHzhkNgbrqlfW4ClwHgRO/YABz2e7iHD4SFcFidFEUvKp7eQPWr39IwNOQ8tBYMzdIHTgebzuI36RaGO0ojEokm5QyIBNnutWuJVQ7AWD3gexqivn+Aoh0WA0pnXq7vI348EvkrQFRVkLDbMpd/FzwYQ8q4HCM/ffVnAN7gZ/kYLOuvc3LypwTkXZOUlZYvzCVg1d9nPxBXj5zxXV/lXDzPyIswX/99yONixC+RA2OCRmeiskEYaSrXN+WY8i7aBrFvLnHQ7IppYGWlhdhjc6YovrUnVR/7mY2ThkMsns9/o24tEUSljT8I/gGGoQIDAQAB"
		
	
);