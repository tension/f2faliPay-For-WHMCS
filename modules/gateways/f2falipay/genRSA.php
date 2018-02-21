<?php
try {
	$type = $_GET['type'];
	
	if ( $type == 'SHA1' ) {
		$size = '1024';
	} else {
		$size = '2048';
	}
    // 生成密钥对
    $resource = openssl_pkey_new([
        "digest_alg" => $type,
        "private_key_bits" => (int) $size,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ]);

    // 获取私钥
    openssl_pkey_export($resource, $privateKey);

    // 获取公钥
    $publicKey = openssl_pkey_get_details($resource)['key'];

    // 如果任意内容为空
    if (empty($privateKey) || empty($publicKey))
        throw new Exception('密钥对生成失败，请重试操作');

    // 返回信息
    $result = [
        'status' => 'success',
        'result' => [
            // 处理内容并复制给数组
            'private' => str_replace([
                PHP_EOL,
                '-----BEGIN PRIVATE KEY-----',
                '-----END PRIVATE KEY-----'
            ], '', $privateKey),
            'public' => str_replace([
//                PHP_EOL,
                '-----BEGIN PUBLIC KEY-----',
                '-----END PUBLIC KEY-----'
            ], '', $publicKey),
        ],
    ];
}
catch (Exception $e) {
    // 返回报错信息
    $result = [
        'status' => 'error',
        'info' => $e->getMessage(),
    ];
}
finally {
    // 以 json 输出
    die(json_encode($result));
}