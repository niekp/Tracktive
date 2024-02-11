<?php

namespace App\Actions;

use phpGPX\phpGPX;

final class MergeGpxFilesAction
{
    public function __invoke(array $files): \SplFileInfo
    {
        libxml_use_internal_errors(true);
        // Try to sort files
        try {
            usort($files, function (string $a, string $b) {
                $a_file = phpGPX::load($a);
                $b_file = phpGPX::load($b);

                $a_point = $a_file->tracks[0]?->segments[0]?->getPoints()[0]?->time;
                $b_point = $b_file->tracks[0]?->segments[0]?->getPoints()[0]?->time;

                return ($a_point < $b_point) ? -1 : 1;
            });

        } catch (\Exception) {
        }

        // Use first file as base.
        $gpx = phpGPX::load($files[0]);
        $files = array_splice($files, 1, count($files) - 1);

        // Add other files as segments
        foreach ($files as $file) {
            $segment_file = phpGPX::load($file);

            foreach ($segment_file->tracks[0]->segments as $segment) {
                $gpx->tracks[0]->segments[] = $segment;
            }
        }

        // Save temporary file
        $temp = tempnam(sys_get_temp_dir(), 'tracktive_');
        $gpx->save($temp, phpGPX::XML_FORMAT);

        return new \SplFileInfo($temp);
    }
}
