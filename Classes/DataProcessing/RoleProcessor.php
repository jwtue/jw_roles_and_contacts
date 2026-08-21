<?php

declare(strict_types=1);

namespace JwTue\RolesAndContacts\DataProcessing;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Content Blocks resolves the "roles" Relation field into one ContentBlockData
 * object per role (since our role table isn't itself a Content-Blocks-defined
 * table, it builds a "fake" wrapper exposing the raw row as properties), but
 * does not recursively resolve the role's own "person" TCA relation (verified
 * on TYPO3 v12 / content-blocks 0.7.21: role.person stays the raw uid in
 * Fluid). This processor replaces that raw uid with the resolved person row,
 * so the existing role/person fallback logic in the Fluid template can access
 * role.person.* fields.
 *
 * ContentBlockData extends \stdClass, so overwriting a property directly
 * (e.g. $role->person = [...]) shadows its magic __get()-based lookup for
 * that key — no need to rebuild or reassign the containing "roles" array.
 */
final class RoleProcessor implements DataProcessorInterface
{
    private const PERSON_TABLE = 'tx_jwrolesandcontacts_domain_model_person';

    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        $contentBlockData = $processedData['data'] ?? null;
        $roles = is_object($contentBlockData) ? ($contentBlockData->roles ?? []) : [];
        if (!is_array($roles) || $roles === []) {
            return $processedData;
        }

        $personUids = array_filter(array_map(
            static fn(object $role): int => (int)($role->person ?? 0),
            $roles
        ));

        $persons = $personUids === [] ? [] : $this->fetchPersons($personUids);

        foreach ($roles as $role) {
            $personUid = (int)($role->person ?? 0);
            $role->person = $persons[$personUid] ?? null;
        }

        return $processedData;
    }

    /**
     * @param int[] $uids
     * @return array<int, array<string, mixed>>
     */
    private function fetchPersons(array $uids): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::PERSON_TABLE);
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(GeneralUtility::makeInstance(HiddenRestriction::class));

        $rows = $queryBuilder
            ->select('*')
            ->from(self::PERSON_TABLE)
            ->where(
                $queryBuilder->expr()->in('uid', $queryBuilder->createNamedParameter($uids, Connection::PARAM_INT_ARRAY))
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $indexed = [];
        foreach ($rows as $row) {
            $row['image'] = $fileRepository->findByRelation(self::PERSON_TABLE, 'image', (int)$row['uid']);
            $indexed[(int)$row['uid']] = $row;
        }

        return $indexed;
    }
}
