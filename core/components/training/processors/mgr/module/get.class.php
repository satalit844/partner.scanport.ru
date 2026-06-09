<?php

require_once dirname(__DIR__) . '/_media_helper.php';

class TrainingModuleGetProcessor extends modObjectGetProcessor
{
    public $classKey = 'TrainingModule';
    public $objectType = 'training.module';

    public function checkPermissions()
    {
        return true;
    }

    public function initialize()
    {
        if (!$this->getProperty('id')) {
            return $this->modx->lexicon('invalid_data');
        }
        return parent::initialize();
    }

    protected function fetchResourceData($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;
        $resource = $this->modx->getObject('modResource', ['id' => $id]);
        if ($resource) {
            return [
                'pagetitle' => $resource->get('pagetitle'),
                'alias' => $resource->get('alias'),
                'context_key' => $resource->get('context_key'),
                'uri' => $resource->get('uri'),
                'published' => (int)$resource->get('published'),
            ];
        }
        $table = $this->modx->getTableName('modResource');
        $stmt = $this->modx->prepare("SELECT pagetitle, alias, context_key, uri, published FROM {$table} WHERE id = :id LIMIT 1");
        if ($stmt && $stmt->execute([':id' => $id])) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $row['published'] = (int)$row['published'];
                return $row;
            }
        }
        return null;
    }

    public function cleanup()
    {
        $array = $this->object->toArray();
        $resourceRow = $this->fetchResourceData($array['resource_id']);
        if ($resourceRow) {
            $array = array_merge($array, $resourceRow);
            $array['published_label'] = !empty($resourceRow['published']) ? 'Да' : 'Нет';
        } else {
            $array['pagetitle'] = '—';
            $array['alias'] = '—';
            $array['context_key'] = '—';
            $array['uri'] = '—';
            $array['published'] = 0;
            $array['published_label'] = 'Нет';
        }

        /** @var TrainingCourse $course */
        $course = $this->modx->getObject('TrainingCourse', ['id' => $array['course_id']]);
        $array['course_title'] = '—';
        if ($course) {
            $courseRow = $this->fetchResourceData($course->get('resource_id'));
            if ($courseRow && !empty($courseRow['pagetitle'])) {
                $array['course_title'] = $courseRow['pagetitle'];
            }
        }

        $array['videos_count'] = (int)$this->modx->getCount('TrainingModuleVideo', ['module_id' => $array['id']]);
        $array['slides_count'] = (int)$this->modx->getCount('TrainingModuleSlide', ['module_id' => $array['id']]);

        $dirs = TrainingMediaHelper::resolveModulePresentationDirs($this->modx, $this->object);
        $slidesDir = !empty($array['slides_dir']) ? (string)$array['slides_dir'] : $dirs['slides_web'];
        $slidesAbs = TrainingMediaHelper::webPathToFs($this->modx, $slidesDir);

        $availableSlidesCount = 0;
        if ($slidesAbs && is_dir($slidesAbs)) {
            foreach ((array)scandir($slidesAbs) as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                $full = rtrim($slidesAbs, '/\\') . '/' . $item;
                if (is_file($full) && preg_match('#\.(jpg|jpeg|png|webp|gif)$#i', $item)) {
                    $availableSlidesCount++;
                }
            }
        }

        $array['id_label'] = $array['id'];
        $array['course_id_label'] = $array['course_id'];
        $array['duration_human'] = $this->formatSeconds((int)$array['duration_seconds']);
        $array['createdon'] = !empty($array['createdon']) ? $array['createdon'] : '—';
        $array['updatedon'] = !empty($array['updatedon']) ? $array['updatedon'] : '—';
        $array['is_active'] = (int)!empty($array['is_active']);
        $array['is_required'] = (int)!empty($array['is_required']);
        $array['source_video'] = (string)$array['source_video'];
        $array['video_status'] = !empty($array['video_status']) ? $array['video_status'] : 'none';
        $array['source_presentation'] = (string)$array['source_presentation'];
        $array['presentation_pdf'] = !empty($array['presentation_pdf']) ? $array['presentation_pdf'] : '—';
        $array['slides_dir'] = $slidesDir;
        $array['slides_dir_exists_label'] = ($slidesAbs && is_dir($slidesAbs)) ? 'Да' : 'Нет';
        $array['presentation_status'] = !empty($array['presentation_status']) ? $array['presentation_status'] : 'none';
        $array['available_slides_count'] = $availableSlidesCount;

        return $this->success('', $array);
    }

    protected function formatSeconds($seconds)
    {
        $seconds = (int)$seconds;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
        return $hours > 0 ? sprintf('%d:%02d:%02d', $hours, $minutes, $seconds) : sprintf('%d:%02d', $minutes, $seconds);
    }
}

return 'TrainingModuleGetProcessor';
