# Eloquent model conventions

All models in `app/Models` follow these rules. Match them when you add or edit a model.

- Declare model configuration with class attributes, not properties: `#[Fillable([...])]`, `#[Hidden([...])]`, `#[Appends([...])]`, `#[Unguarded]`, `#[WithoutTimestamps]`, `#[Table(...)]`, `#[Connection(...)]` (all from `Illuminate\Database\Eloquent\Attributes`). Why: this keeps a model's configuration in one place above the class.
- Declare casts in a `protected function casts(): array` method. Never use a `$casts` property.
- Write local scopes as `#[Scope] protected function name(Builder $query, ...): void`. Do not use the `scopeName` prefix. Callers still use `Model::name()`.
- Write accessors as `protected function name(): Attribute { return Attribute::make(get: ...); }`. Do not write `getNameAttribute` methods.
- Do not add IDE-helper `@property` / `@method` docblocks. A short class docblock that explains what the model stores, and anything non-obvious, is welcome.

## MongoDB models

- Mongo models extend `MongoDB\Laravel\Eloquent\Model` and carry `#[Connection('mongodb')]` (account snapshots, loot, group bank) or `#[Connection('mongodb-static')]` (read-only game data: `Item`), plus `#[Table(key: '_id')]`.
- A new model on the `mongodb` connection must also be added to the per-test cleanup list in `tests/Pest.php`. Why: `RefreshDatabase` only rolls back SQL, so Mongo documents would otherwise leak between tests. If the model is account-scoped, it must also be added to `ResetInstanceData`.
- `Account` (Postgres) cannot have Eloquent relations to Mongo documents. Its snapshots (`inventory`, `lootingBag`, …) are `Attribute::make(get: fn () => Model::where('account_id', $this->id)->first())->withoutObjectCaching()`. Why: `withoutObjectCaching()` keeps a read that follows a plugin push fresh on the same instance.

## Deliberate exceptions (do not "fix")

- `Item` keeps `protected $keyType = 'int'`. The Mongo base model declares a string key type, and the `Table` attribute cannot override it.
- `Membership` keeps `public $incrementing = true`. A `#[Table]` attribute would also reset the `team_user` table name that the Jetstream parent declares. The class looks unused but is not: Jetstream's `$membershipModel` defaults to `App\Models\Membership`, and `$user->teams()` resolves the pivot table through it. Deleting it breaks every team query.
- `Team` keeps its `$dispatchesEvents` map for the Jetstream team events.
