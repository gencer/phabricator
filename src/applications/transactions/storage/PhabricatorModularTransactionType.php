<?php

abstract class PhabricatorModularTransactionType
  extends Phobject {

  private $storage;
  private $viewer;
  private $editor;

  final public function getTransactionTypeConstant() {
    return $this->getPhobjectClassConstant('TRANSACTIONTYPE');
  }

  public function generateOldValue($object) {
    throw new PhutilMethodNotImplementedException();
  }

  public function generateNewValue($object, $value) {
    return $value;
  }

  public function validateTransactions($object, array $xactions) {
    return array();
  }

  public function willApplyTransactions($object, array $xactions) {
    return;
  }

  public function applyInternalEffects($object, $value) {
    return;
  }

  public function applyExternalEffects($object, $value) {
    return;
  }

  public function extractFilePHIDs($object, $value) {
    return array();
  }

  public function shouldHide() {
    return false;
  }

  public function getIcon() {
    return null;
  }

  public function getTitle() {
    return null;
  }

  public function getTitleForFeed() {
    return null;
  }

  public function getColor() {
    return null;
  }

  public function hasChangeDetailView() {
    return false;
  }

  public function newChangeDetailView() {
    throw new PhutilMethodNotImplementedException();
  }

  final public function setStorage(
    PhabricatorApplicationTransaction $xaction) {
    $this->storage = $xaction;
    return $this;
  }

  private function getStorage() {
    return $this->storage;
  }

  final public function setViewer(PhabricatorUser $viewer) {
    $this->viewer = $viewer;
    return $this;
  }

  final protected function getViewer() {
    return $this->viewer;
  }

  final public function getActor() {
    return $this->getEditor()->getActor();
  }

  final public function getActingAsPHID() {
    return $this->getEditor()->getActingAsPHID();
  }

  final public function setEditor(
    PhabricatorApplicationTransactionEditor $editor) {
    $this->editor = $editor;
    return $this;
  }

  final protected function getEditor() {
    if (!$this->editor) {
      throw new PhutilInvalidStateException('setEditor');
    }
    return $this->editor;
  }

  final protected function getAuthorPHID() {
    return $this->getStorage()->getAuthorPHID();
  }

  final protected function getObjectPHID() {
    return $this->getStorage()->getObjectPHID();
  }

  final protected function getObject() {
    return $this->getStorage()->getObject();
  }

  final protected function getOldValue() {
    return $this->getStorage()->getOldValue();
  }

  final protected function getNewValue() {
    return $this->getStorage()->getNewValue();
  }

  final protected function renderAuthor() {
    $author_phid = $this->getAuthorPHID();
    return $this->getStorage()->renderHandleLink($author_phid);
  }

  final protected function renderObject() {
    $object_phid = $this->getObjectPHID();
    return $this->getStorage()->renderHandleLink($object_phid);
  }

  final protected function renderHandle($phid) {
    $viewer = $this->getViewer();
    $display = $viewer->renderHandle($phid);

    $rendering_target = $this->getStorage()->getRenderingTarget();
    if ($rendering_target == PhabricatorApplicationTransaction::TARGET_TEXT) {
      $display->setAsText(true);
    }

    return $display;
  }

  final protected function renderHandleList(array $phids) {
    $viewer = $this->getViewer();
    $display = $viewer->renderHandleList($phids)
      ->setAsInline(true);

    $rendering_target = $this->getStorage()->getRenderingTarget();
    if ($rendering_target == PhabricatorApplicationTransaction::TARGET_TEXT) {
      $display->setAsText(true);
    }

    return $display;
  }

  final protected function renderValue($value) {
    $rendering_target = $this->getStorage()->getRenderingTarget();
    if ($rendering_target == PhabricatorApplicationTransaction::TARGET_TEXT) {
      return sprintf('"%s"', $value);
    }

    return phutil_tag(
      'span',
      array(
        'class' => 'phui-timeline-value',
      ),
      $value);
  }

  final protected function renderOldValue() {
    return $this->renderValue($this->getOldValue());
  }

  final protected function renderNewValue() {
    return $this->renderValue($this->getNewValue());
  }

  final protected function renderDate($epoch) {
    $viewer = $this->getViewer();

    $display = phabricator_datetime($epoch, $viewer);

    // TODO: When rendering for email, include the UTC offset. See T10633.

    return $this->renderValue($display);
  }

  final protected function renderOldDate() {
    return $this->renderDate($this->getOldValue());
  }

  final protected function renderNewDate() {
    return $this->renderDate($this->getNewValue());
  }

  final protected function newError($title, $message, $xaction = null) {
    return new PhabricatorApplicationTransactionValidationError(
      $this->getTransactionTypeConstant(),
      $title,
      $message,
      $xaction);
  }

  final protected function newRequiredError($message, $xaction = null) {
    return $this->newError(pht('Required'), $message, $xaction)
      ->setIsMissingFieldError(true);
  }

  final protected function newInvalidError($message, $xaction = null) {
    return $this->newError(pht('Invalid'), $message, $xaction);
  }

  final protected function isNewObject() {
    return $this->getEditor()->getIsNewObject();
  }

  final protected function isEmptyTextTransaction($value, array $xactions) {
    foreach ($xactions as $xaction) {
      $value = $xaction->getNewValue();
    }

    return !strlen($value);
  }


}