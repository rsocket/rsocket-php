<?php


namespace RSocket\core;


use RSocket\ConnectionSetupPayload;
use RSocket\frame\SetupFrame;

class RSocketBaseResponder
{

    protected function parseSetupPayload(SetupFrame $setupFrame): ConnectionSetupPayload
    {
        $setupPayload = new ConnectionSetupPayload();
        $setupPayload->setDataMimeType($setupFrame->dataMimeType);
        $setupPayload->setMetadataMimeType($setupFrame->metadataMimeType);
        $setupPayload->setKeepAliveInterval($setupFrame->keepAliveInterval);
        $setupPayload->setKeepAliveMaxLifetime($setupFrame->keepAliveMaxLifetime);
        if (!is_null($setupFrame->payload)) {
            $setupPayload->metadata = $setupFrame->payload->metadata;
            $setupPayload->data = $setupFrame->payload->data;
        }
        return $setupPayload;
    }
}