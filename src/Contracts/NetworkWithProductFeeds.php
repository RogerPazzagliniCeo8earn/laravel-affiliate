<?php

namespace SoluzioneSoftware\LaravelAffiliate\Contracts;

use SoluzioneSoftware\LaravelAffiliate\Models\Feed;

interface NetworkWithProductFeeds extends Network
{
    public function downloadFeeds(string $path, callable $progressCallback);

    public function downloadFeedProducts(Feed $feed, string $path, callable $progressCallback);

    public function mapProductRow(array $row): array;

    public function mapProductFeedRow(array $row): array;
}
