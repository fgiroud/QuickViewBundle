<?php

namespace Fgir\QuickViewBundle\Adapter;

use Doctrine\ODM\MongoDB\DocumentManager;

class DoctrineOdmAdapter
{
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function findClassesMatching($match)
    {
        $metadatas = $this->dm->getMetadataFactory()->getAllMetadata();
        $possibilities = [];

        foreach ($metadatas as $metadata) {
            $name = $metadata->getName();
            if (strpos($name, $match) !== false) {
                $possibilities[] = $name;
            }
        }

        return $possibilities;
    }

    public function findOne($className, $id)
    {
        $document = $this->dm->getRepository($className)->find($id);
        return $document;
    }

    public function findGte($className, $primaryKey, $limit)
    {
        $metadatas = $this->dm->getClassMetadata($className);
        $primaryField = $metadatas->getIdentifier($className)[0];
        $documents = $this->dm->getRepository($className)->createQueryBuilder('doc')
            ->field($primaryField)->gte($primaryKey)
        // ->limit($limit)
            ->getQuery()
            ->execute()
            ->toArray()
        ;
        // sd($documents);

        return $documents;
    }
}
