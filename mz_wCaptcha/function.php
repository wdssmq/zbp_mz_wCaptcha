<?php
// 验证不通过时的错误处理
function mz_wCaptcha_error($message)
{
  if (GetVars('format', 'POST') == 'json')
    JsonError(79, $message, $_POST);
  else
    ScriptError($message);
}

// HTTP 请求封装
function mz_wCaptcha_http($prove)
{
  global $zbp;
  $apiKey = $zbp->Config('wCaptcha')->apiKey;
  $data = array(
    "api_key" => $apiKey,
    "prove" => $prove
  );

  $http = Network::Create();
  $http->open('POST', 'https://api-wcaptcha.pingflash.com/captcha/verify');
  $http->send($data);

  Logs($http->responseText);

  return json_decode($http->responseText);
}
