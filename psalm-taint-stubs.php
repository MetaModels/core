<?php

/**
 * Taint annotations for Psalm's taint analysis (psalm-taint.xml).
 *
 * Contao, DcGeneral and Doctrine are not annotated upstream, so the sources, escapes and sinks used by MetaModels
 * are declared here. Only the signature parts needed by the annotations are repeated.
 */

namespace Contao {
    class Input
    {
        /**
         * Input::get() and friends strip tags and encode entities unless a raw variant is used.
         *
         * @psalm-taint-source input
         * @psalm-taint-escape html
         * @psalm-taint-escape has_quotes
         */
        public static function get($strKey, $blnDecodeEntities = false, $blnKeepUnused = false)
        {
        }

        /**
         * @psalm-taint-source input
         * @psalm-taint-escape html
         * @psalm-taint-escape has_quotes
         */
        public static function post($strKey, $blnDecodeEntities = false)
        {
        }

        /**
         * @psalm-taint-source input
         * @psalm-taint-escape html
         * @psalm-taint-escape has_quotes
         */
        public static function findGet($strKey)
        {
        }

        /**
         * @psalm-taint-source input
         * @psalm-taint-escape html
         * @psalm-taint-escape has_quotes
         */
        public static function findPost($strKey)
        {
        }

        /** @psalm-taint-source input */
        public static function postRaw($strKey)
        {
        }

        /** @psalm-taint-source input */
        public static function postHtml($strKey, $blnDecodeEntities = false)
        {
        }

        /** @psalm-taint-source input */
        public static function cookie($strKey)
        {
        }
    }

    class StringUtil
    {
        /**
         * @psalm-taint-escape html
         * @psalm-taint-escape has_quotes
         */
        public static function specialchars($strString, $blnStripInsertTags = false, $blnDoubleEncode = false)
        {
        }

        /**
         * @psalm-taint-escape html
         * @psalm-taint-escape has_quotes
         */
        public static function specialcharsAttribute($strString, $blnStripInsertTags = false, $blnDoubleEncode = false)
        {
        }

        /**
         * @psalm-taint-escape html
         * @psalm-taint-escape has_quotes
         */
        public static function specialcharsUrl($strString)
        {
        }
    }

    class Database
    {
        /** @psalm-taint-sink sql $strQuery */
        public function prepare($strQuery)
        {
        }

        /** @psalm-taint-sink sql $strQuery */
        public function query($strQuery)
        {
        }

        /** @psalm-taint-sink sql $strQuery */
        public function execute($strQuery)
        {
        }
    }
}

namespace ContaoCommunityAlliance\DcGeneral {
    interface InputProviderInterface
    {
        /** @psalm-taint-source input */
        public function getValue($name, $forceRawValue = false);

        /** @psalm-taint-source input */
        public function getParameter($name, $raw = false);
    }
}

namespace Symfony\Component\HttpFoundation {
    class InputBag
    {
        /** @psalm-taint-source input */
        public function get(string $key, string|int|float|bool|null $default = null)
        {
        }

        /** @psalm-taint-source input */
        public function all(?string $key = null): array
        {
        }
    }

    class Response
    {
        /** @psalm-taint-sink html $content */
        public function __construct(?string $content = '', int $status = 200, array $headers = [])
        {
        }

        /** @psalm-taint-sink html $content */
        public function setContent(?string $content): static
        {
        }
    }
}

namespace Doctrine\DBAL {
    class Connection
    {
        /** @psalm-taint-sink sql $sql */
        public function executeQuery(string $sql, array $params = [], array $types = [], $qcp = null)
        {
        }

        /** @psalm-taint-sink sql $sql */
        public function executeStatement($sql, array $params = [], array $types = [])
        {
        }

        /** @psalm-taint-sink sql $sql */
        public function prepare(string $sql)
        {
        }

        /** @psalm-taint-sink sql $sql */
        public function exec(string $sql)
        {
        }

        /** @psalm-taint-sink sql $sql */
        public function fetchAllAssociative(string $query, array $params = [], array $types = [])
        {
        }

        /** @psalm-taint-sink sql $query */
        public function fetchAssociative(string $query, array $params = [], array $types = [])
        {
        }

        /** @psalm-taint-sink sql $query */
        public function fetchOne(string $query, array $params = [], array $types = [])
        {
        }

        /** @psalm-taint-sink sql $query */
        public function fetchFirstColumn(string $query, array $params = [], array $types = [])
        {
        }

        /** @psalm-taint-escape sql */
        public function quote($value, $type = \Doctrine\DBAL\ParameterType::STRING)
        {
        }

        /** @psalm-taint-escape sql */
        public function quoteIdentifier(string $str)
        {
        }
    }
}

namespace Doctrine\DBAL\Query {
    class QueryBuilder
    {
        /** @psalm-taint-sink sql $predicate */
        public function where($predicate, ...$predicates)
        {
        }

        /** @psalm-taint-sink sql $predicate */
        public function andWhere($predicate, ...$predicates)
        {
        }

        /** @psalm-taint-sink sql $predicate */
        public function orWhere($predicate, ...$predicates)
        {
        }

        /**
         * @psalm-taint-sink sql $sort
         * @psalm-taint-sink sql $order
         */
        public function orderBy($sort, $order = null)
        {
        }

        /**
         * @psalm-taint-sink sql $sort
         * @psalm-taint-sink sql $order
         */
        public function addOrderBy($sort, $order = null)
        {
        }

        /** @psalm-taint-sink sql $expr */
        public function groupBy($expr, ...$exprs)
        {
        }

        /** @psalm-taint-sink sql $having */
        public function having($having, ...$havings)
        {
        }

        /** @psalm-taint-escape sql */
        public function createNamedParameter($value, $type = \Doctrine\DBAL\ParameterType::STRING, $placeHolder = null)
        {
        }

        /** @psalm-taint-escape sql */
        public function createPositionalParameter($value, $type = \Doctrine\DBAL\ParameterType::STRING)
        {
        }
    }
}
