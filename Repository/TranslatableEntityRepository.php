<?php


namespace VKR\TranslationBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use VKR\TranslationBundle\Interfaces\LocaleRetrieverInterface;
use Doctrine\ORM\Query\Expr;

class TranslatableEntityRepository extends EntityRepository
{

    /**
     * @var LocaleRetrieverInterface
     */
    private $localeRetriever;

    /**
     * @param LocaleRetrieverInterface $localeRetriever
     *
     * @return $this;
     */
    public function setLocaleRetriever($localeRetriever)
    {
        $this->localeRetriever = $localeRetriever;

        return $this;
    }

    public function findAll()
    {
        try {

            return $this->createQueryBuilderWithBasicJoins()
                ->getQuery()
                ->getResult();
        } catch (NoResultException $e) {

            return null;
        }
    }

    public function find($id, $lockMode = null, $lockVersion = null)
    {
        if (is_array($id)) {

            throw new \InvalidArgumentException('Composite keys are not supported');
        }

        $qb = $this->createQueryBuilderWithBasicJoins();

        $query = $qb->where($qb->expr()->eq('e.id', ':id'))
            ->setParameter(':id', $id)
            ->getQuery();

        $query->setLockMode($lockMode);

        try {

            return $query->getSingleResult();
        } catch (NoResultException $e) {

            return null;
        }

    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQueryBuilderWithBasicJoins()
    {
        $qb = $this->createQueryBuilder('e');

        try {
            return $qb->leftJoin('e.translations', 't')
                ->join('t.language', 'l', Expr\Join::WITH, $qb->expr()->eq('l.code', ':language_code'))
                ->select('e', 't', 'l')
                ->setParameter(':language_code', $this->localeRetriever->getCurrentLocale());
        } catch (\Exception $e) {

            return $qb;
        }

    }

}