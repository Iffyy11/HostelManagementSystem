<?php

namespace App\Database;

use Illuminate\Database\Connectors\PostgresConnector;

/**
 * Neon routes connections by the SNI hostname. Older libpq builds (such as the one
 * bundled with the Vercel PHP runtime) do not send it, and Neon then rejects the
 * connection with "Endpoint ID is not specified". Passing the endpoint ID as a
 * startup option works with and without SNI, so add it for Neon hosts.
 *
 * The endpoint defaults to the first label of the host (ep-xxx or ep-xxx-pooler);
 * set DB_NEON_ENDPOINT to override it.
 */
class NeonPostgresConnector extends PostgresConnector
{
    protected function getDsn(array $config)
    {
        $dsn = parent::getDsn($config);

        $host = (string) ($config['host'] ?? '');
        $endpoint = $config['neon_endpoint'] ?? null;

        if (! $endpoint && str_ends_with($host, '.neon.tech')) {
            $endpoint = strtok($host, '.');
        }

        return $endpoint ? $dsn.";options='endpoint=".$endpoint."'" : $dsn;
    }
}
