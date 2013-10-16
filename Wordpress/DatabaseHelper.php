<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\Wordpress;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;

class DatabaseHelper
{

    protected $connection;
    protected $tablePrefix;
    protected $kernel;

    public function __construct(Connection $connection, $tablePrefix, HttpKernel $kernel)
    {
        $this->connection = $connection;
        $this->tablePrefix = $tablePrefix;
        $this->kernel = $kernel;
    }

    public function getTermBySlug($slug)
    {
        return $this->connection->fetchAssoc(
            "
            SELECT
                *
            FROM
                {$this->tablePrefix}terms t
            WHERE
                slug = :slug COLLATE utf8_bin
        ",
            array('slug' => $slug)
        );
    }

    public function changeTermName($id, $name)
    {
        $this->connection->executeQuery(
            "
            UPDATE
                {$this->tablePrefix}terms
            SET
                name = :name
            WHERE
                term_id = :id
        ",
            array('id' => $id, 'name' => $name)
        );
    }

    public function changeTermSlug($id, $slug)
    {
        $this->connection->executeQuery(
            "
            UPDATE
                {$this->tablePrefix}terms
            SET
                slug = :slug
            WHERE
                term_id = :id
        ",
            array('id' => $id, 'slug' => $slug)
        );
    }

    public function getTaxonomy($id)
    {
        return $this->connection->fetchAssoc(
            "
            SELECT
                *
            FROM
                {$this->tablePrefix}term_taxonomy tax
            JOIN
                {$this->tablePrefix}terms t ON tax.term_id = t.term_id
            WHERE
                tax.term_taxonomy_id = :id
        ",
            array('id' => $id)
        );
    }

    public function listTaxonomies($like, $type)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->addSelect('*');
        $queryBuilder->from("{$this->tablePrefix}terms", 't');
        $queryBuilder->innerJoin('t', "{$this->tablePrefix}term_taxonomy", 'tax', 'tax.term_id = t.term_id');
        $queryBuilder->addOrderBy('t.slug');
        $queryBuilder->addOrderBy('t.name');

        if ($like) {
            $queryBuilder->andWhere('(t.slug LIKE :like OR t.name LIKE :like)');
            $queryBuilder->setParameter('like', "%$like%");
        }

        if ($type) {
            $queryBuilder->andWhere('tax.taxonomy = :type');
            $queryBuilder->setParameter('type', $type);
        }

        return $this->connection->fetchAll($queryBuilder->getSQL(), $queryBuilder->getParameters());
    }

    public function copyTaxonomyRelations($sourceId, $destinationId)
    {
        $source = $this->getTaxonomy($sourceId);

        $this->connection->executeQuery(
            "
            INSERT IGNORE INTO
                {$this->tablePrefix}term_relationships
            (object_id, term_taxonomy_id, term_order)
            SELECT
                object_id,
                :destinationId,
                term_order
            FROM
                {$this->tablePrefix}term_relationships
            WHERE
                term_taxonomy_id = :sourceId
        ",
            array('sourceId' => $sourceId, 'destinationId' => $destinationId)
        );

        $taxonomy = $this->connection->fetchColumn("
            SELECT
                taxonomy
            FROM
                {$this->tablePrefix}term_taxonomy
            WHERE
                term_taxonomy_id = :id
        ", array('id' => $destinationId));

        $this->connection->executeQuery("
            UPDATE
                {$this->tablePrefix}term_taxonomy
            SET
                count = count + :count
            WHERE
                term_taxonomy_id = :id
        ", array('id' => $destinationId, 'count' => $source['count']));
    }

    public function removeTaxonomy($id) {
        $this->connection->executeQuery("
            DELETE FROM
                {$this->tablePrefix}term_relationships
            WHERE
                term_taxonomy_id = :id
        ", array('id' => $id));

        $this->connection->executeQuery("
            DELETE FROM
                {$this->tablePrefix}term_taxonomy
            WHERE
                term_taxonomy_id = :id
        ", array('id' => $id));

        $this->connection->executeQuery("
            DELETE FROM
                {$this->tablePrefix}terms
            WHERE NOT EXISTS(
                SELECT 1 FROM {$this->tablePrefix}term_taxonomy WHERE term_id = {$this->tablePrefix}terms.term_id
            )
        ");
    }

    public function mergeTaxonomies($sourceId, $targetId)
    {
        $this->copyTaxonomyRelations($sourceId, $targetId);
        $this->removeTaxonomy($sourceId);
    }

}