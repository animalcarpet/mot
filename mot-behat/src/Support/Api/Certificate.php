<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\HttpClient;

class Certificate extends MotApi
{
    const PATH = '/certificate-print/{:motTestNumber}';
    const PATH_DUPE_CERT = 'certificate-print/100000010240/dup?siteNr=V1234';

    public function requestCertificate($motTestNumber, $accessToken)
    {
        $path = str_replace('{:motTestNumber}', $motTestNumber, self::PATH);
        $result = $this->pdfRequest($accessToken, 'POST', $path, ['motTestId' => $motTestNumber]);

        return (string)$result->getRawBody();
    }

    public function getDuplicateCertificate($token)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ];

        return $this->get(self::PATH_DUPE_CERT, $headers);
    }
}
