<?php 
//高德地图
	$arrayName = array(
				'10000' => array(
						'en' => 'OK',
						'cn' => '请求正常,请求正常'
						),
				'10001' => array(
						'en' => 'INVALID_USER_KEY',
						'cn' => 'key不正确或过期,开发者发起请求时，传入的key不正确或者过期 '
						),
				'10002' => array(
						'en' => 'SERVICE_NOT_AVAILABLE',
						'cn' => '没有权限使用相应的服务或者请求接口的路径拼写错误,1.开发者没有权限使用相应的服务，例如：发者申请了WEB定位功能的key，却使用该key访问逆地理编码功能时，就会返回该错误。反之亦然。2.开发者请求接口的路径拼写错误。例如：正确的https://restapi.amap.com/v3/ip在程序中被拼装写了https://restapi.amap.com/vv3/ip"'
						),
				'10003' => array(
						'en' => 'DAILY_QUERY_OVER_LIMIT',
						'cn' => '访问已超出日访问量,开发者的日访问量超限，被系统自动封停，第二天0:00会自动解封。'
						),
				'10004' => array(
						'en' => 'ACCESS_TOO_FREQUENT',
						'cn' => '单位时间内访问过于频繁,开发者的单位时间内（1分钟）访问量超限，被系统自动封停，下一分钟自动解封。'
						),
				'10005' => array(
						'en' => 'INVALID_USER_IP',
						'cn' => 'IP白名单出错，发送请求的服务器IP不在IP白名单内,开发者在LBS官网控制台设置的IP白名单不正确。名单中未添加对应服务器的出口IP。可到"控制台>配置"  中设定IP白名单。'
						),
				'10006' => array(
						'en' => 'INVALID_USER_DOMAIN',
						'cn' => '绑定域名无效,开发者绑定的域名无效，需要在官网控制台重新设置'
						),
				'10007' => array(
						'en' => 'INVALID_USER_SIGNATURE',
						'cn' => '数字签名未通过验证,开发者签名未通过开发者在key控制台中，开启了“数字签名”功能，没有按照指定算法生成“数字签名”。'
						),
				
				'10009' => array(
						'en' => 'USERKEY_PLAT_NOMATCH',
						'cn' => '请求key与绑定平台不符,请求中使用的key与绑定平台不符，例如：开发者申请的是js api的key，用来调web服务接口'
						),
				'10008' => array(
						'en' => 'INVALID_USER_SCODE',
						'cn' => 'MD5安全码未通过验证,需要开发者判定key绑定的SHA1,package是否与sdk包里的一致'
						),
				
				'10010' => array(
						'en' => 'IP_QUERY_OVER_LIMIT',
						'cn' => 'IP访问超限,未设定IP白名单的开发者使用key发起请求，从单个IP向服务器发送的请求次数超出限制，系统自动封停。封停后无法自动恢复，需要提交工单联系我们。'
						),
				
				'10011' => array(
						'en' => 'NOT_SUPPORT_HTTPS',
						'cn' => '服务不支持https请求,服务不支持https请求，如果需要申请支持，请提交工单联系我们'
						),
				'10012' => array(
						'en' => 'INSUFFICIENT_PRIVILEGES',
						'cn' => '权限不足，服务请求被拒绝,由于不具备请求该服务的权限，所以服务被拒绝。'
						),
				'10013' => array(
						'en' => 'USER_KEY_RECYCLED',
						'cn' => 'Key被删除,开发者删除了key，key被删除后无法正常使用'
						),
				'10014' => array(
						'en' => 'QPS_HAS_EXCEEDED_THE_LIMIT',
						'cn' => '云图服务QPS超限,QPS超出限制，超出部分的请求被拒绝。限流阈值内的请求依旧会正常返回'
						),
				'10015' => array(
						'en' => 'GATEWAY_TIMEOUT',
						'cn' => '受单机QPS限流限制,受单机QPS限流限制时出现该问题，建议降低请求的QPS或在控制台提工单联系我们'
						),
				'10016' => array(
						'en' => 'SERVER_IS_BUSY',
						'cn' => '服务器负载过高,服务器负载过高，请稍后再试'
						),
				'10017' => array(
						'en' => 'RESOURCE_UNAVAILABLE',
						'cn' => '所请求的资源不可用,所请求的资源不可用'
						),
				'10019' => array(
						'en' => 'CQPS_HAS_EXCEEDED_THE_LIMIT',
						'cn' => '使用的某个服务总QPS超限,QPS超出限制，超出部分的请求被拒绝。限流阈值内的请求依旧会正常返回'
						),
				'10020' => array(
						'en' => 'CKQPS_HAS_EXCEEDED_THE_LIMIT',
						'cn' => '某个Key使用某个服务接口QPS超出限制,QPS超出限制，超出部分的请求被拒绝。流阈值内的请求依旧会正常返回'
						),
				
				'10021' => array(
						'en' => 'CIQPS_HAS_EXCEEDED_THE_LIMIT',
						'cn' => '来自于同一IP的访问，使用某个服务QPS超出限制,QPS超出限制，超出部分的请求被拒绝。流阈值内的请求依旧会正常返回'
						),
				
				'10022' => array(
						'en' => 'CIKQPS_HAS_EXCEEDED_THE_LIMIT',
						'cn' => '某个Key，来自于同一IP的访问，使用某个服务QPS超出限制,QPS超出限制，超出部分的请求被拒绝。流阈值内的请求依旧会正常返回'
						),
				
				'10023' => array(
						'en' => 'KQPS_HAS_EXCEEDED_THE_LIMIT',
						'cn' => '某个KeyQPS超出限制,QPS超出限制，超出部分的请求被拒绝。限流阈值内的请求依旧会正常返回'
						),
				
				'20000' => array(
						'en' => 'INVALID_PARAMS',
						'cn' => '某个KeyQPS超出限制,QPS超出限制，超出部分的请求被拒绝。限流阈值内的请求依旧会正常返回'
						),
				'10023' => array(
						'en' => 'KQPS_HAS_EXCEEDED_THE_LIMIT',
						'cn' => '请求参数非法,请求参数的值没有按照规范要求填写。例如，某参数值域范围为[1,3],开发者误填了’4’'
						),
				
						'200 => array(
								'en'  ISSING_REQUIRED_PARAMS',
						'cn' => '缺少必填参数,缺少接口中要求的必填参数'
						),
				'20002' => array(
						'en' => 'ILLEGAL_REQUEST',
						'cn' => '请求协议非法,比如某接口仅支持get请求，结果用了POST方式'
						),
				'20003' => array(
						'en' => 'UNKNOWN_ERROR',
						'cn' => '其他未知错误'
						),
				'20800' => array(
						'en' => 'OUT_OF_SERVICE',
						'cn' => '规划点（包括起点、终点、途经点）不在中国陆地范围内,使用路径规划服务接口时可能出现该问题，规划点（包括起点、终点、途经点）不在中国陆地范围内'
						),
				'20801' => array(
						'en' => 'NO_ROADS_NEARBY',
						'cn' => '划点（起点、终点、途经点）附近搜不到路,使用路径规划服务接口时可能出现该问题，划点（起点、终点、途经点）附近搜不到路'
						),
				'20802' => array(
						'en' => 'ROUTE_FAIL',
						'cn' => '路线计算失败，通常是由于道路连通关系导致,使用路径规划服务接口时可能出现该问题，路线计算失败，通常是由于道路连通关系导致'
						),
				'20803' => array(
						'en' => 'OVER_DIRECTION_RANGE',
						'cn' => '起点终点距离过长。使用路径规划服务接口时可能出现该问题，路线计算失败，通常是由于道路起点和终点距离过长导致。'
						),
				'300**' => array(
						'en' => 'ENGINE_RESPONSE_DATA_ERROR',
						'cn' => '服务响应失败。出现3开头的错误码，建议先检查传入参数是否正确，若无法解决，请详细描述错误复现信息，提工单给我们。（大数据接口请直接跟负责商务反馈）如，30001、30002、30003、32000、32001、32002、32003、32200、32201、32202、32203。'
						)
);