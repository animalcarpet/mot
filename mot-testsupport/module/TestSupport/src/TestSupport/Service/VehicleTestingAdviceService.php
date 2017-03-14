<?php
namespace TestSupport\Service;

use Doctrine\ORM\EntityManager;

class VehicleTestingAdviceService
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function create($vehicleId, $modelId, $category, array $content)
    {
        $modelTechnicalDataId = $this->createModelTechnicalData($modelId);
        $categoryId = $this->createModelTechnicalDataCategory($category);
        $this->createModelTechnicalDataContent($modelTechnicalDataId, $categoryId, $content);
        $this->createVehicleModelTechnicalDataMap($modelTechnicalDataId, $vehicleId);
    }

    public function createDefault($vehicleId, $modelId)
    {
        $content = [
            "Most common engines have 4, 6, or 8 pistons which move up and down in the cylinders.",
            "The crankshaft is connected to the pistons via a connecting rod."
        ];

        $this->create($vehicleId, $modelId, "Engine", $content);
    }

    private function createModelTechnicalData($modelId)
    {
        $this->em->getConnection()->insert("model_technical_data",
        [
            "model_id" => $modelId,
            "created_by" =>$this->getUserId()
        ]);

        return $this->em->getConnection()->lastInsertId();
    }

    private function createModelTechnicalDataCategory($name)
    {
        $max = (int) $this->em->getConnection()->fetchColumn("SELECT MAX(`display_order`) FROM model_technical_data_category");
        $order = $max+1;

        $this->em->getConnection()->insert("model_technical_data_category",
            [
                "name" => $name,
                "display_order" => $order,
                "created_by" =>$this->getUserId()
            ]);

        return $this->em->getConnection()->lastInsertId();
    }

    private function createModelTechnicalDataContent($modelTechnicalDataId, $categoryId, array $content)
    {
        $order = 1;
        foreach ($content as $description) {
            $this->em->getConnection()->insert("model_technical_data_content",
                [
                    "short_description" => "The Automotive Engine",
                    "description" => $description,
                    "display_order" => $order,
                    "model_technical_data_category_id" => $categoryId,
                    "created_by" =>$this->getUserId()
                ]);

            $contentId = $this->em->getConnection()->lastInsertId();

            $order++;

            $this->em->getConnection()->insert("model_technical_data_content_map",
                [
                    "model_technical_data_id" => $modelTechnicalDataId,
                    "model_technical_data_content_id" => $contentId,
                    "created_by" =>$this->getUserId()
                ]);
        }

    }

    private function createVehicleModelTechnicalDataMap($modelTechnicalDataId, $vehicleId)
    {
        $this->em->getConnection()->insert("vehicle_model_technical_data_map",
            [
                "vehicle_id" => $vehicleId,
                "model_technical_data_id" => $modelTechnicalDataId,
                "created_by" =>$this->getUserId()
            ]);
    }

    private function getUserId()
    {
        return (int) $this->em->getConnection()->fetchColumn("SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data'");
    }
}
