<?php

namespace App\Service\Claim;

use Api\Service\Initializers\ApiClientInterface;
use Api\Service\Initializers\ApiClientTrait;
use Exception;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Log as LogModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Verification as VerificationModel;

class Claim implements ApiClientInterface
{
    use ApiClientTrait;

    /**
     * Retrieves the next available, unassigned Claim, assigns it to the currently logged in user and returns it
     *
     * @param int $userId user id to assign claim to
     * @return int the id of the next case to process. Will be zero if none was assigned
     */
    public function assignNextClaim(int $userId)
    {
        //  GET on caseworker's case endpoint without an id means get next refund case
        $result = $this->getApiClient()->httpPut("/v1/cases/user/$userId/claim", []);

        return $result['assignedClaimId'];
    }

    /**
     * @param int $claimId
     * @param int $userId
     * @return ClaimModel
     * @throws Exception
     */
    public function getClaim(int $claimId, int $userId)
    {
        $claimData = $this->getApiClient()->httpGet("/v1/cases/claim/$claimId");

        $claim = $this->createDataModel($claimData);

        if (!$claim instanceof ClaimModel || $claim->getAssignedToId() !== $userId) {
            //User is not assigned to chosen claim
            throw new Exception('Access forbidden', 403);
        }

        return $claim;
    }

    /**
     * Get all claims
     *
     * @return array
     */
    public function getClaims()
    {
        $claimsData = $this->getApiClient()->httpGet('/v1/cases/claim');

        return $this->createModelCollection($claimsData);
    }

    /**
     * @param int $claimId
     * @param string $title the new log's title
     * @param string $message the new log's message
     * @return LogModel the newly created log
     */
    public function addLog(int $claimId, string $title, string $message)
    {
        $logArray = $this->getApiClient()->httpPost("/v1/cases/claim/$claimId/log", [
            'title'   => $title,
            'message' => $message,
        ]);

        if (empty($logArray)) {
            return null;
        }

        return new LogModel($logArray);
    }

    /**
     * @param int $claimId
     * @param bool $noSiriusPoas
     * @return null|ClaimModel
     */
    public function setNoSiriusPoas(int $claimId, bool $noSiriusPoas)
    {
        $claimArray = $this->getApiClient()->httpPatch("/v1/cases/claim/$claimId", [
            'noSiriusPoas' => $noSiriusPoas
        ]);

        return $this->createDataModel($claimArray);
    }

    /**
     * @param int $claimId
     * @param bool $noMerisPoas
     * @return null|ClaimModel
     */
    public function setNoMerisPoas(int $claimId, bool $noMerisPoas)
    {
        $claimArray = $this->getApiClient()->httpPatch("/v1/cases/claim/$claimId", [
            'noMerisPoas' => $noMerisPoas
        ]);

        return $this->createDataModel($claimArray);
    }

    /**
     * @param ClaimModel $claim
     * @param PoaModel $poa
     * @return null|ClaimModel
     */
    public function addPoa(ClaimModel $claim, PoaModel $poa)
    {
        $this->updatePoaCaseNumberVerification($claim, $poa);

        $claimArray = $this->getApiClient()->httpPost("/v1/cases/claim/{$claim->getId()}/poa", $poa->getArrayCopy());

        return $this->createDataModel($claimArray);
    }

    public function editPoa(ClaimModel $claim, PoaModel $poa, int $poaId)
    {
        $this->updatePoaCaseNumberVerification($claim, $poa);

        $claimArray = $this->getApiClient()->httpPut("/v1/cases/claim/{$claim->getId()}/poa/{$poaId}", $poa->getArrayCopy());

        return $this->createDataModel($claimArray);
    }

    public function deletePoa($claimId, $poaId)
    {
        $claimArray = $this->getApiClient()->httpDelete("/v1/cases/claim/{$claimId}/poa/{$poaId}");

        return $this->createDataModel($claimArray);
    }

    public function setRejectionReason(int $claimId, $rejectionReason, $rejectionReasonDescription)
    {
        $claimArray = $this->getApiClient()->httpPatch("/v1/cases/claim/$claimId", [
            'status'                     => ClaimModel::STATUS_REJECTED,
            'rejectionReason'            => $rejectionReason,
            'rejectionReasonDescription' => $rejectionReasonDescription
        ]);

        return $this->createDataModel($claimArray);
    }

    public function setStatusAccepted(int $claimId)
    {
        $claimArray = $this->getApiClient()->httpPatch("/v1/cases/claim/$claimId", [
            'status' => ClaimModel::STATUS_ACCEPTED
        ]);

        return $this->createDataModel($claimArray);
    }

    /**
     * @param ClaimModel $claim
     * @param PoaModel $poa
     */
    private function updatePoaCaseNumberVerification(ClaimModel $claim, PoaModel $poa)
    {
        $poaCaseNumber = $claim->getApplication()->getCaseNumber()->getPoaCaseNumber();

        if ($poaCaseNumber !== null) {
            if ($poaCaseNumber === $poa->getCaseNumber()) {
                //Add verification for case number
                $verifications = $poa->getVerifications();
                $verifications[] = new VerificationModel([
                    'type' => VerificationModel::TYPE_CASE_NUMBER,
                    'passes' => 'yes'
                ]);
                $poa->setVerifications($verifications);
            }
        }
    }

    /**
     * Create model from array data
     *
     * @param array|null $data
     * @return null|ClaimModel
     */
    private function createDataModel(array $data = null)
    {
        if (is_array($data) && !empty($data)) {
            return new ClaimModel($data);
        }

        return null;
    }

    /**
     * Create a collection (array) of models
     *
     * @param array|null $data
     * @return array
     */
    private function createModelCollection(array $data = null)
    {
        $models = [];

        if (is_array($data)) {
            foreach ($data as $dataItem) {
                $models[] = $this->createDataModel($dataItem);
            }
        };

        return $models;
    }
}