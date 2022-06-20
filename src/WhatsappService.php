<?php

namespace navidman\whatsappApi;

use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class WhatsappService
{
    private $file_path = null;
    public function __construct()
    {

    }
    public function sendMessage($data)
    {
        dd('package worked Navidjan!');
        $base64_url = null;
        $isImage = null;
        $isFile = null;
        $image_available_formats = config('whatsapp.image_available_formats');
        $file_available_formats = config('whatsapp.file_available_formats');
        $file = $data->file('file');
        if ($file) {
            $extension = $data->file->getClientOriginalExtension();
            $isImage = in_array($extension, $image_available_formats);
            $isFile = in_array($extension, $file_available_formats);
            $file_name = time() . $file->getClientOriginalName();
            $base64_url = $this->urlBuilder($isImage, $isFile, $file_name, $file, $extension);
            if (!$isFile && !$isImage) {
                return response('فرمت فایل ارسالی اشتباه است!',Response::HTTP_BAD_REQUEST);
            }
        } else {
            $this->file_path = null;
        }
        $result = $this->httpRequestMaker($data, $base64_url);
        switch ($result) {
            case -1:
                return response(config('whatsapp.available_responses.-1'),Response::HTTP_BAD_REQUEST);
                break;
            case -2:
                return response(config('whatsapp.available_responses.-2'),Response::HTTP_BAD_REQUEST);
                break;
            case -3:
                return response(config('whatsapp.available_responses.-3'),Response::HTTP_BAD_REQUEST);
                break;
            case -4:
                return response(config('whatsapp.available_responses.-4'),Response::HTTP_BAD_REQUEST);
                break;
            case -5:
                return response(config('whatsapp.available_responses.-5'),Response::HTTP_BAD_REQUEST);
                break;
            case -6:
                return response(config('whatsapp.available_responses.-6'),Response::HTTP_BAD_REQUEST);
                break;
            case -7:
                return response(config('whatsapp.available_responses.-7'),Response::HTTP_BAD_REQUEST);
                break;
            case -8:
                return response(config('whatsapp.available_responses.-8'),Response::HTTP_BAD_REQUEST);
                break;
            case -9:
                return response(config('whatsapp.available_responses.-9'),Response::HTTP_BAD_REQUEST);
                break;
            case -10:
                return response(config('whatsapp.available_responses.-10'),Response::HTTP_BAD_REQUEST);
                break;
            case -11:
                return response(config('whatsapp.available_responses.-11'),Response::HTTP_BAD_REQUEST);
                break;
            case -15:
                return response(config('whatsapp.available_responses.-12'),Response::HTTP_BAD_REQUEST);
                break;
            default:
                break;
        }
        if($result == 0) {
            $message = $this->insertMessage($data, $this->file_path, $isImage, $isFile);
        }
        return response($message,Response::HTTP_OK);
    }

    private function base64Converter($file)
    {
        $base64 = base64_encode(file_get_contents($file->path()));
        return $base64;
    }

    private function insertMessage($data, $file_path, $isImage, $isFile)
    {
        $message = WhatsappMessage::create([
            'sender' => config('whatsapp.sender'),
            'receivers' => $data->receivers ? $data->receivers : null,
            'group_id' => $data->group_id ? $data->group_id : null,
            'message' => $data->message,
            'image' => $isImage ? $file_path : null,
            'file' => $isFile ? $file_path : null,
        ]);
        return $message;
    }

    private function storeFile($file, $file_name, $path)
    {
        return Storage::disk('public')->putFileAs(
            $path,
            $file,
            $file_name
        );
    }

    private function urlBuilder($isImage, $isFile, $file_name, $file, $extension)
    {
        if ($isImage) {
            $this->file_path= 'storage/whatsapp/image/' . $file_name;
            $path = 'whatsapp/image/';
            $this->storeFile($file, $file_name, $path);
            $base64 = $this->base64Converter($file);
            $base64_url = 'data:image/' . $extension . ';base64,' . $base64;
            return $base64_url;
        }
        if ($isFile) {
            $this->file_path= 'storage/whatsapp/file/' . $file_name;
            $path = 'whatsapp/file/';
            $this->storeFile($file, $file_name, $path);
            $base64 = $this->base64Converter($file);
            $base64_url = 'data:file/' . $extension . ';base64,' . $base64;
            return $base64_url;
        }
        return response('فرمت فایل ارسالی اشتباه است!',Response::HTTP_BAD_REQUEST);
    }

    private function headerBuilder($data)
    {
        if ($data->receivers) {
            $headers = [
                "Content-Type" => "application/json",
                "Accept" => "application/json",
                "sender" => config('whatsapp.sender'),
                "key" => config('whatsapp.key'),
                "receivers" => $data->receivers
            ];
        }
        if ($data->group_id) {
            $headers = [
                "Content-Type" => "application/json",
                "Accept" => "application/json",
                "sender" => config('whatsapp.sender'),
                "key" => config('whatsapp.key'),
                "groupId" => $data->group_id
            ];
        }
        return $headers;
    }

    private function bodyBuilder($message, $base64_url)
    {
        $body = [
            "message" => $message,
            "imgBase64" => $base64_url ? $base64_url : ''
        ];
        return $body;
    }

    private function httpRequestMaker($data, $base64_url)
    {
        $body = $this->bodyBuilder($data->message, $base64_url);
        $headers = $this->headerBuilder($data);

        $client = new Client();
        $response = $client->request('POST', 'https://wesender.ir/Send', [
            'headers' => $headers,
            'body' => json_encode($body)
        ]);
        $result = json_decode($response->getBody(), true);
        return $result;
    }
}
