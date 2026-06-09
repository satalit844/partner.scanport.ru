<?php

require_once dirname(__DIR__) . '/_media_helper.php';

class TrainingCourseGetProcessor extends modObjectGetProcessor
{
    public $classKey = 'TrainingCourse';
    public $objectType = 'training.course';

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
        if ($id <= 0) {
            return null;
        }
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
        $row = $this->fetchResourceData($array['resource_id']);
        if ($row) {
            $array = array_merge($array, $row);
            $array['published_label'] = !empty($row['published']) ? 'Да' : 'Нет';
        } else {
            $array['pagetitle'] = '—';
            $array['alias'] = '—';
            $array['context_key'] = '—';
            $array['uri'] = '—';
            $array['published'] = 0;
            $array['published_label'] = 'Нет';
        }

        $array['id_label'] = $array['id'];
        $array['modules_count'] = (int)$this->modx->getCount('TrainingModule', ['course_id' => $array['id']]);

        $dirs = TrainingMediaHelper::resolveCoursePresentationDirs($this->modx, $this->object);
        $slidesCount = 0;
        if (!empty($dirs['slides_absolute']) && is_dir($dirs['slides_absolute'])) {
            foreach ((array)scandir($dirs['slides_absolute']) as $item) {
                if ($item === '.' || $item === '..') continue;
                if (is_file($dirs['slides_absolute'] . $item) && preg_match('#\.(jpg|jpeg|png|webp|gif)$#i', $item)) {
                    $slidesCount++;
                }
            }
        }

        $array['is_active'] = (int)!empty($array['is_active']);
        $array['source_presentation'] = (string)$array['source_presentation'];
        $array['presentation_pdf'] = !empty($array['presentation_pdf']) ? $array['presentation_pdf'] : '—';
        $array['slides_dir'] = !empty($array['slides_dir']) ? $array['slides_dir'] : $dirs['slides_web'];
        $array['slides_dir_exists_label'] = (!empty($array['slides_dir']) && is_dir(TrainingMediaHelper::webPathToFs($this->modx, $array['slides_dir']))) ? 'Да' : 'Нет';
        $array['presentation_status'] = !empty($array['presentation_status']) ? $array['presentation_status'] : 'none';
        $array['available_slides_count'] = $slidesCount;

        return $this->success('', $array);
    }
}

return 'TrainingCourseGetProcessor';
