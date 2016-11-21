<?php

namespace Dvsa\Mot\Behat\Support\Api;

class Certificate extends MotApi
{
    const PATH = '/certificate-print/{:motTestNumber}';

    public function requestCertificate($motTestNumber, $accessToken)
    {
        $path = str_replace('{:motTestNumber}', $motTestNumber, self::PATH);
        $result = $this->pdfRequest($accessToken, 'POST', $path, ['motTestId' => $motTestNumber]);

        return (string)$result->getRawBody();
    }
}