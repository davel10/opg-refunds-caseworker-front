<?php

namespace Beta\Action;

use App\Action\AbstractApiClientAction;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Stream;

/**
 * Class DownloadAction
 * @package Beta\Action
 */
class DownloadAction extends AbstractApiClientAction
{

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $applications = $this->getApiClient()->getApplications();

        $csvResource = fopen('php://output', 'w');
        foreach ($applications as $idx => $application) {
            $flattened = array_intersect_key($application, [
                'id' => 0,
                'applicant' => 0,
                'submitted' => 0,
                'expected' => 0
            ]);

            $flattened['donor.title'] = $application['donor']['name']['title'];
            $flattened['donor.first-name'] = $application['donor']['name']['first'];
            $flattened['donor.last-name'] = $application['donor']['name']['last'];
            $flattened['donor.dob'] = $application['donor']['dob'];

            $flattened['attorney.title'] = $application['attorney']['name']['title'];
            $flattened['attorney.first-name'] = $application['attorney']['name']['first'];
            $flattened['attorney.last-name'] = $application['attorney']['name']['last'];
            $flattened['attorney.dob'] = $application['attorney']['dob'];

            $flattened['verification.case-number'] = $application['verification']['case-number'];
            $flattened['verification.donor-postcode'] = $application['verification']['donor-postcode'];
            $flattened['verification.attorney-postcode'] = $application['verification']['attorney-postcode'];

            $flattened['contact.email'] = $application['contact']['email'];
            $flattened['contact.mobile'] = $application['contact']['mobile'];

            $flattened['account.name'] = $application['account']['name'];
            $flattened['account.number'] = $application['account']['details']['account-number'];
            $flattened['account.sort-code'] = $application['account']['details']['sort-code'];

            if ($idx === 0) {
                fputcsv($csvResource, array_keys($flattened));
            }
            fputcsv($csvResource, $flattened);
        }

        $stream = new Stream($csvResource);
        $timestamp = time();
        $fileName = "Applications_{$timestamp}.csv";

        $response = new Response();

        return $response
            ->withHeader('Content-Type', 'application/vnd.ms-excel')
            ->withHeader(
                'Content-Disposition',
                "attachment; filename=" . basename($fileName)
            )
            ->withHeader('Content-Transfer-Encoding', 'Binary')
            ->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Pragma', 'public')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withBody($stream)
            ->withHeader('Content-Length', "{$stream->getSize()}");
    }
}