# Caching Pattern

## CacheKey & DAL
### API
#### BaseModel

* $model->findOne("sql cond str")
* BaseModel::findCachedOne(CacheKey $k)
* $model->save()
* BaseModel::saveObjects

#### BaseMapper

* BaseMapper::saveAssociation
* BaseMapper::getCachedObjects

#### CacheKey
* CacheKey::q("[db_name.]entity?complex-conditions")
* CacheKey::c("complex-conditions")
* getType/getDBName/getEntity
* \_and/_or
* asc/desc/limit
* getOrderInfo/getLimitation
* copy
* test/match

#### DAL
* DAL::get
* DAL::getListCount
* DAL::delete
* DAL::addToList
* DAL::deleteFromList
* DAL::s

## Key-Value
* each model as a `hash` in redis
* the key format: `db_name.entity_name?attr_name=value`  e.g.:
  - `account.user?id=2`
  - `account.user?username=kdr2@shopin.com`
  - `store_419.product?id=327`
* use class `CacheKey` to manage/generate the cache key
* read model: just call `BaseModel::findCachedOne` or `DAL::get` with
  a CacheKey instance, if the data is not in cache, `DAL::get` will
  goto database and then fill the cache before returning the data
* update model: we do not have a set/update method for update KV, when
  you call `$model->save()`, the cache is automatically updated by
  deleting the outdated cache and the getting the lastest data by
  calling `DAL::get`. If you update a model by a raw SQL(usually in a
  mapper-class), you should call `DAL::delete` to delete the outdated
  cache manually, and you will get the lastest data while you call
  `DAL::get` next time.

## Key-List
### Introduction
* a key-list is a collection of entites who meets certain
  conditions(described by the `CacheKey`)
* in redis, the `Key-List`s are lists/sets, with the `Key-Value` cache
  keys as their elements.
* the format of `Key-List`'s key: `[db_name.]entites?conditions...`
* we define some pre-defined lists in
  `quickstart/application/cachekey_lists.php`, these lists will be
  maintained automatically or semiautomatically:
  - if you use `$model->save()` or `BaseModel::saveObjects(...)`, the
    lists are maintained automactically
  - if you use a raw SQL to update a model(usually in a mapper-class),
    you should use `DAL::s` to sync the lists, do this is easy,
    there's an example in `ProductsMapper::deleteProductCategory`.
  - so the lists are maintained automactically in BaseModel, or
    semiautomatically in mapper-class, we do not write code about list
    maintenance in any controller or service.
* read list: just call `BaseMapper::getCachedObjects` or `DAL::get`
  with a `CacheKey` instance.

### Pre-Defined Lists
    see `quickstart/application/cachekey_lists.php`

### Call Stack

* $model->findOne():
  goto databse directly

* BaseModel::findCachedOne
  ```
                           ModelClass::format
  DAL::getKV -> DAL::get --------------------->  BaseModel::findCachedOne ->
     .
    /|\
     |
     |-->-- Redis
     |
     |----- MapperClass::getCachedObject <---- Database

  ```
