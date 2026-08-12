<?php
// Desativado em 2026-08: pedidos agora sao feitos em
// https://puncher.com/app/novo-pedido/
// Handler antigo preservado no historico git (antes deste commit).
http_response_code(410);
header('Content-Type: application/json');
echo json_encode(array(
    'success' => false,
    'message' => 'This form has been retired. Please place your order at https://puncher.com/app/novo-pedido/'
));
exit;
