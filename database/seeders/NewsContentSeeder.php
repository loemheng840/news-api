<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class NewsContentSeeder extends Seeder
{
    /**
     * Seed sample data for article creation flow.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@news.local'],
            [
                'name' => 'News Admin',
                'password' => Hash::make('password'),
                'role' => 'ADMIN',
                'status' => true,
            ]
        );

        $author = User::updateOrCreate(
            ['email' => 'author@news.local'],
            [
                'name' => 'News Author',
                'password' => Hash::make('password'),
                'role' => 'AUTHOR',
                'status' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'reader@news.local'],
            [
                'name' => 'News Reader',
                'password' => Hash::make('password'),
                'role' => 'READER',
                'status' => true,
            ]
        );

        $categoryNames = [
            'Technology',
            'Politics',
            'Business',
            'World',
            'Sports',
            'Entertainment',
        ];

        $categories = collect($categoryNames)->mapWithKeys(function (string $name) {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );

            return [$category->slug => $category];
        });

        $tagNames = [
            'AI',
            'Startup',
            'Economy',
            'Elections',
            'Climate',
            'Innovation',
            'Cybersecurity',
            'Markets',
        ];

        $tags = collect($tagNames)->mapWithKeys(function (string $name) {
            $tag = Tag::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );

            return [$tag->slug => $tag];
        });

        $sampleArticles = [
            [
                'title' => 'AI Tools Are Changing Local Newsrooms',
                'category_slug' => 'technology',
                'tag_slugs' => ['ai', 'innovation'],
                'status' => 'PUBLISHED',
                'author_id' => $author->id,
            ],
            [
                'title' => 'Startup Funding Rebounds in Southeast Asia',
                'category_slug' => 'business',
                'tag_slugs' => ['startup', 'markets'],
                'status' => 'PUBLISHED',
                'author_id' => $author->id,
            ],
            [
                'title' => 'Election Campaigns Move Heavily to Social Platforms',
                'category_slug' => 'politics',
                'tag_slugs' => ['elections', 'innovation'],
                'status' => 'DRAFT',
                'author_id' => $admin->id,
            ],
            [
                'title' => 'Climate Policies Reshape Global Trade Talks',
                'category_slug' => 'world',
                'tag_slugs' => ['climate', 'economy'],
                'status' => 'PUBLISHED',
                'author_id' => $admin->id,
            ],
            [
                'title' => 'Cybersecurity Teams Brace for AI-Powered Phishing',
                'category_slug' => 'technology',
                'tag_slugs' => ['cybersecurity', 'ai'],
                'status' => 'PUBLISHED',
                'author_id' => $author->id,
            ],
            [
                'title' => 'Government Unveils Digital Identity Roadmap',
                'category_slug' => 'politics',
                'tag_slugs' => ['innovation', 'elections'],
                'status' => 'PUBLISHED',
                'author_id' => $admin->id,
            ],
            [
                'title' => 'Regional Banks Tighten Lending for Early Startups',
                'category_slug' => 'business',
                'tag_slugs' => ['startup', 'economy'],
                'status' => 'PUBLISHED',
                'author_id' => $author->id,
            ],
            [
                'title' => 'Global Summit Focuses on Climate Adaptation Funds',
                'category_slug' => 'world',
                'tag_slugs' => ['climate', 'economy'],
                'status' => 'PUBLISHED',
                'author_id' => $admin->id,
            ],
            [
                'title' => 'National League Announces Midseason Rule Updates',
                'category_slug' => 'sports',
                'tag_slugs' => ['markets', 'innovation'],
                'status' => 'PUBLISHED',
                'author_id' => $author->id,
            ],
            [
                'title' => 'Streaming Platforms Compete for Local Drama Rights',
                'category_slug' => 'entertainment',
                'tag_slugs' => ['markets', 'startup'],
                'status' => 'PUBLISHED',
                'author_id' => $admin->id,
            ],
            [
                'title' => 'How Small Teams Build News Apps in Weeks',
                'category_slug' => 'technology',
                'tag_slugs' => ['startup', 'innovation'],
                'status' => 'DRAFT',
                'author_id' => $author->id,
            ],
            [
                'title' => 'Parliament Debates New Media Transparency Law',
                'category_slug' => 'politics',
                'tag_slugs' => ['elections', 'economy'],
                'status' => 'DRAFT',
                'author_id' => $admin->id,
            ],
            [
                'title' => 'Retail Sales Signal Cautious Consumer Confidence',
                'category_slug' => 'business',
                'tag_slugs' => ['markets', 'economy'],
                'status' => 'PUBLISHED',
                'author_id' => $author->id,
            ],
            [
                'title' => 'New Trade Corridor Could Cut Regional Shipping Costs',
                'category_slug' => 'world',
                'tag_slugs' => ['economy', 'markets'],
                'status' => 'PUBLISHED',
                'author_id' => $admin->id,
            ],
            [
                'title' => 'Youth Championship Draws Record Crowds This Season',
                'category_slug' => 'sports',
                'tag_slugs' => ['innovation', 'markets'],
                'status' => 'PUBLISHED',
                'author_id' => $author->id,
            ],
            [
                'title' => 'Award Season Highlights New Independent Filmmakers',
                'category_slug' => 'entertainment',
                'tag_slugs' => ['innovation', 'startup'],
                'status' => 'DRAFT',
                'author_id' => $admin->id,
            ],
            [
                'title' => 'Universities Launch AI Ethics Programs for Journalists',
                'category_slug' => 'technology',
                'tag_slugs' => ['ai', 'climate'],
                'status' => 'PUBLISHED',
                'author_id' => $author->id,
            ],
            [
                'title' => 'Election Polling Methods Face New Scrutiny',
                'category_slug' => 'politics',
                'tag_slugs' => ['elections', 'ai'],
                'status' => 'PUBLISHED',
                'author_id' => $admin->id,
            ],
            [
                'title' => 'Manufacturing Index Beats Forecast in Q2',
                'category_slug' => 'business',
                'tag_slugs' => ['economy', 'markets'],
                'status' => 'DRAFT',
                'author_id' => $author->id,
            ],
            [
                'title' => 'Neighbors Sign Climate Cooperation Pact',
                'category_slug' => 'world',
                'tag_slugs' => ['climate', 'innovation'],
                'status' => 'PUBLISHED',
                'author_id' => $admin->id,
            ],
            [
                'title' => 'Women League Expands with Two New Clubs',
                'category_slug' => 'sports',
                'tag_slugs' => ['startup', 'markets'],
                'status' => 'PUBLISHED',
                'author_id' => $author->id,
            ],
            [
                'title' => 'Music Festivals Rebound with Hybrid Tickets',
                'category_slug' => 'entertainment',
                'tag_slugs' => ['innovation', 'markets'],
                'status' => 'PUBLISHED',
                'author_id' => $admin->id,
            ],
            [
                'title' => 'Cloud Outage Pushes Firms Toward Multi-Region Design',
                'category_slug' => 'technology',
                'tag_slugs' => ['cybersecurity', 'innovation'],
                'status' => 'PUBLISHED',
                'author_id' => $author->id,
            ],
            [
                'title' => 'Cabinet Approves Budget for Rural Connectivity',
                'category_slug' => 'politics',
                'tag_slugs' => ['economy', 'innovation'],
                'status' => 'PUBLISHED',
                'author_id' => $admin->id,
            ],
            [
                'title' => 'Fintechs Race to Offer Lower-Cost Remittances',
                'category_slug' => 'business',
                'tag_slugs' => ['startup', 'markets'],
                'status' => 'PUBLISHED',
                'author_id' => $author->id,
            ],
            [
                'title' => 'International Courts Tackle Cross-Border Data Disputes',
                'category_slug' => 'world',
                'tag_slugs' => ['cybersecurity', 'elections'],
                'status' => 'DRAFT',
                'author_id' => $admin->id,
            ],
            [
                'title' => 'Coaches Adopt Data Models for Injury Prevention',
                'category_slug' => 'sports',
                'tag_slugs' => ['ai', 'innovation'],
                'status' => 'PUBLISHED',
                'author_id' => $author->id,
            ],
            [
                'title' => 'Studios Invest in Virtual Production Pipelines',
                'category_slug' => 'entertainment',
                'tag_slugs' => ['ai', 'startup'],
                'status' => 'PUBLISHED',
                'author_id' => $admin->id,
            ],
        ];

        foreach ($sampleArticles as $data) {
            $slug = Str::slug($data['title']);
            $category = $categories->get($data['category_slug']);

            $article = Article::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $data['title'],
                    'content' => $this->makeArticleBody($data['title']),
                    'status' => $data['status'],
                    'category_id' => $category?->id,
                    'author_id' => $data['author_id'],
                    'published_at' => $data['status'] === 'PUBLISHED' ? now()->subDays(rand(1, 12)) : null,
                ]
            );

            $tagIds = collect($data['tag_slugs'])
                ->map(fn (string $slugValue) => $tags->get($slugValue)?->id)
                ->filter()
                ->values()
                ->all();

            if (!empty($tagIds)) {
                $article->tags()->syncWithoutDetaching($tagIds);
            }
        }
    }

    private function makeArticleBody(string $title): string
    {
        return "{$title}\n\n"
            ."This is seeded sample content so you can test article listing, search, filtering, and detail pages right away.\n\n"
            ."You can edit this article from the dashboard, add thumbnails, switch draft/published status, and assign more tags/categories.";
    }
}
