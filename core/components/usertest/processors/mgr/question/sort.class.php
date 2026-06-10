<?php

class UserTestQuestionSortProcessor extends modObjectProcessor {
	public $classKey = 'UserTestQuestions';
	private $test_id;
	private $parent;

	/** {@inheritDoc} */
	public function process() {
		/* @var msProduct $source */
		$source = $this->modx->getObject($this->classKey, $this->getProperty('source'));
		/* @var msProduct $target */
		$target = $this->modx->getObject($this->classKey, $this->getProperty('target'));

		if (empty($source) || empty($target)) {
			return $this->modx->error->failure();
		}
		$this->test_id = $source->get('test_id');
		$this->parent = $source->get('parent');
		
		if ($source->get('menuindex') < $target->get('menuindex')) {
			$this->modx->exec("UPDATE {$this->modx->getTableName($this->classKey)}
				SET menuindex = menuindex - 1 WHERE
					menuindex <= {$target->get('menuindex')}
					AND menuindex > {$source->get('menuindex')}
					AND menuindex > 0
			");

		} else {
			$this->modx->exec("UPDATE {$this->modx->getTableName($this->classKey)}
				SET menuindex = menuindex + 1 WHERE
					menuindex >= {$target->get('menuindex')}
					AND menuindex < {$source->get('menuindex')}
			");
		}
		$newRank = $target->get('menuindex');
		$source->set('menuindex',$newRank);
		$source->save();
		
		if ($this->modx->getCount($this->classKey, array('menuindex' => 0, 'test_id' => $this->test_id,'parent'=>$this->parent))) {
			//echo $this->test_id; exit;
			$this->setIndex();
		}
		return $this->modx->error->success();
	}


	/** {@inheritDoc} */
	public function setIndex() {
		$q = $this->modx->newQuery($this->classKey, array('test_id' => $this->test_id,'parent'=>$this->parent));
		$q->select('id');
		$q->sortby('menuindex ASC, id', 'ASC');

		if ($q->prepare() && $q->stmt->execute()) {
			$ids = $q->stmt->fetchAll(PDO::FETCH_COLUMN);
			$sql = '';
			$table = $this->modx->getTableName($this->classKey);
			foreach ($ids as $k => $id) {
				$k1 = $k + 1;
				$sql .= "UPDATE {$table} SET `menuindex` = '{$k1}' WHERE `id` = '{$id}';";
			}
			$this->modx->exec($sql);
		}
	}

}

return 'UserTestQuestionSortProcessor';