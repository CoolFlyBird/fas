<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 成功
     * @author huxinlu
     * @param array $data 返回数据
     * @return \Illuminate\Http\JsonResponse
     */
    public function success($data = null)
    {
        if ($data === null) {
            $data = (object)[];
        }
        return response()->json([
            'code'    => 1000,
            'message' => config('errorcode.code')[1000],
            'data'    => $data,
        ])->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    /**
     * 失败
     * @author huxinlu
     * @param string $msg 错误信息
     * @param $code int 错误编码
     * @return \Illuminate\Http\JsonResponse
     */
    public function fail($msg = '', $code = 3001)
    {
        if (empty($msg)) {
            $msg = config('errorcode.code')[(int) $code];
        }

        return response()->json([
            'code'    => $code,
            'message' => $msg,
            'data'    => (object)[],
        ])->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }
}
