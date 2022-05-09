<?php
declare(strict_types=1);
namespace App\Controller\v1;
use App\Constants\ErrorCode;
use Hyperf\Config\Annotation\Value;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use League\Flysystem\Filesystem;

/**
 * Class UploadController
 * @Controller()
 * @package App\Controller\v1
 */
class UploadController extends AbstractController
{
    /**
     * @Value("aws.bucket")
     */
    private $bucket;

    /**
     * upload images
     * @RequestMapping(path="uploads", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param Filesystem $filesystem
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function uploads(RequestInterface $request, ResponseInterface $response, Filesystem $filesystem)
    {
        $stream = null;
        try {
            if (!$request->hasFile('name')){
                throw new \Exception('Upload type error');
            }
            $file = $request->file('name');
            $ext = $file->getExtension();
            // 新的文件名
            $path = date('YmdHis') . mt_rand(100,1000).md5($ext) . "." . $ext;
            $stream = fopen($file->getRealPath(), 'r+');
            // 保存
            $filesystem->writeStream(
                $path,
                $stream
            );
            fclose($stream);
            $filesystem->setVisibility($path, "public");
            return $response->json(['code' => 200, 'msg' => 'ok', 'data' => sprintf("https://%s.s3.amazonaws.com/%s", $this->bucket, $path)]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        } finally {
            // 释放
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }
}
