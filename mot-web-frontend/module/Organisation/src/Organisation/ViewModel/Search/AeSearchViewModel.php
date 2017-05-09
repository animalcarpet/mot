<?php

namespace Organisation\ViewModel\Search;

use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilderWeb;

/**
 * Class AeSearchViewModel.
 */
class AeSearchViewModel
{
    /**
     * @var bool
     */
    private $aeFound = true;
    /**
     * @var string
     */
    private $search = '';
    /**
     * @var int
     */
    private $maxSearchLength;
    /**
     * @var string
     */
    private $errorMessage;

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        if ($this->aeFound === false) {
            return 'AE Number was not found';
        }

        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxSearchLength()
    {
        return $this->maxSearchLength;
    }

    /**
     * @param int $maxSearchLength
     */
    public function setMaxSearchLength($maxSearchLength)
    {
        $this->maxSearchLength = $maxSearchLength;
    }

    /**
     * @return bool
     */
    public function isAeFound()
    {
        return $this->aeFound;
    }

    /**
     * @param bool $aeFound
     *
     * @return $this
     */
    public function setIsAeFound($aeFound)
    {
        $this->aeFound = $aeFound;

        return $this;
    }

    /**
     * @param int $id
     *
     * @return string
     */
    public function getDetailPage($id)
    {
        return AuthorisedExaminerUrlBuilderWeb::of($id);
    }

    /**
     * @return string
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @param string $search
     *
     * @return $this
     */
    public function setSearch($search)
    {
        $this->search = $search;

        return $this;
    }
}
