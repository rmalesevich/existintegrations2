<?php

namespace App\Objects\Toggl;

use Spatie\DataTransferObject\DataTransferObject;

class TogglProjectDTO extends DataTransferObject
{
    /** @var \App\Objects\Toggl\TogglProjectDataDTO[]|null */
    public $data;

    /**
     * Toggl Projects API doesn't contain a name for the array, so this
     * static method will create the DTO
     */
    public static function fromRequest(array $projectResponse): self
    {
        $data = array();

        foreach ($projectResponse as $projectObject) {
            array_push($data, new TogglProjectDataDTO($projectObject));
        }

        $projects = new TogglProjectDTO();
        $projects->data = $data;

        return $projects;
    }
}