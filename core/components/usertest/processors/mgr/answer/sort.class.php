<?php

class UserTestAnswersSortProcessor extends modObjectProcessor {
	public $classKey = 'UserTestAnswers';
	private $question_id;


	/** {@inheritDoc} */
	public function process() {
		/* @var msProduct $source */
		$source = $this->modx->getObject($this->classKey, $this->getProperty('source'));
		/* @var msProduct $target */
		$target = $this->modx->getObject($this->classKey, $this->getProperty('target'));

		if (empty($source) || empty($target)) {
			return $this->modx->error->failure();
		}
		$this->question_id = $source->get('question_id');

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
		
		if ($this->modx->getCount($this->classKey, array('menuindex' => 0, 'question_id' => $this->question_id))) {
			$this->setIndex();
		}
		return $this->modx->error->success();
	}


	/** {@inheritDoc} */
	public function setIndex() {
		$q = $this->modx->newQuery($this->classKey, array('question_id' => $this->question_id));
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

return 'UserTestAnswersSortProcessor';