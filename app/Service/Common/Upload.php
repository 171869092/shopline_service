<?php
declare(strict_types=1);
namespace App\Service\Common;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Hyperf\Config\Annotation\Value;
use Aws\Credentials\Credentials;

class Upload{
    /**
     * @Value("aws.id")
     */
    private $awsId;

    /**
     * @Value("aws.secret")
     */
    private $awsSecret;

    /**
     * @Value("aws.bucket")
     */
    private $bucket;

    /**
     * upload img
     * @param $file
     * @return string
     */
    public function up($file) :string
    {
        $fileName = $file['name'];
        $fileName = explode('.',$fileName);
        $lastFileName = end($fileName);
        $lastFileNames = date('YmdHis').mt_rand(100,1000).md5($lastFileName[0]);
        $uploadPath = BASE_PATH."/runtime/uploads/imgs/";  // 取得临时文件路径
        #. Rename file
        if (!file_exists($uploadPath)){
            @mkdir($uploadPath, 0777, true);
        }

        $path = $uploadPath . $lastFileNames . '.' . $lastFileName;
        $imgPath = $lastFileNames. '.' .$lastFileName;

        $urlPath = $path . $imgPath;
        $urlPaths = str_replace("\\", "/",$urlPath);//绝对路径，上传第二个参数
        if (!is_uploaded_file($file['tmp_name'])){
            if(!move_uploaded_file($file['tmp_file'],$path)){
                //移动到临时目录里
                throw new S3Exception('Upload Fail');
            }
        }
        $credentials = new Credentials($this->awsId,$this->awsSecret);
        $client = new S3Client([
            'region'  => 'us-east-1',
            'version' => 'latest',
            'signature_version' => 'v4',
            'credentials' => $credentials
        ]);
        $client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $lastFileNames,
            'SourceFile' => $urlPaths,
//                'ContentType' => 'images/jpg/png/jpeg',
            'ACL' => 'public-read',
            'StorageClass' => 'REDUCED_REDUNDANCY'
        ]);
        return $client->getObjectUrl($this->bucket,$lastFileNames);
    }
}
