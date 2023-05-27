# Brazhnikov Illia (23 years)

## Education 👨‍🎓:
- School 2006-2015
- Zaporizhzhya Electrotechnical College (software engeneering) 2015-2019
- Zaporizhzhya National Technical University (software engeneering) 2019-2022

## Work expirience 💼:
- **SolidSolution** (Full Stack Developer) June 2018 - October 2018
- **DevIT** - (Full Stack Developer) October 2018 - April 2021
- **Mine Finance Group** - (Full Stack Developer) April 2021 - March 2022
- **MagneticOne** - (Laravel Developer) April 2022 -present

## Tools 🛠:

### BackEnd:
- PHP 5.6 (6 months)
- PHP 7.x (3 years)
- PHP 8.x (2 years)
- Laravel (5 years)
- WordPress (6 months)
- Drupal 7 (6 months)
- Express (2 months)
- MySQL
- Redis

### FrontEnd:
- EsmaScript5+ (3 years)
- React (2 years)
- Angular (3 months)
- Vue (6 months)
- jQuery (3 years)
- TypeScript (2 years)
- webpack
- gulp

### Other:
- GIT
- Docker
- Linux (Debian-like)
- Swagger
- Apiato
- Postman

## Work with API ☁️:
- AWS
- Google (maps, firebase, oauth)
- Payment systems

## Languages 🏁:
- Ucrainian (native speaker)
- Russian (native speaker)
- English (intermediate)
- Polish (pre-intermediate)

## Additional Information 🗒:
- responsible
- creative
- sociable
- attentive to the details
- open to new knowledge and skills
- having leadership and management skills

## Code examples ⌨️:

### TypeScript:
```TypeScript
type ObserverCallback = (time: number) => void  
  
type ObserverSubscriber = {  
  period: number;  
  nextCall: number;  
  callback: ObserverCallback;  
  alias: string;  
}  
  
class Observer {  
  private subscribers: ObserverSubscriber[] = [];  
  
 private timeout?: number;  
  
 constructor () {  
    this.setTimeout()  
  }  
  
  public subscribe (callback: ObserverCallback, alias: string, period: number = 1): void {  
    period = Math.round(period)  
  
    if (period < 1) {  
      console.error('Observer: Period must be grates than 1')  
      return  
  }  
  
    this.subscribers.push({  
      period,  
  alias,  
  callback,  
  nextCall: Observer.getCurrentTimestampInSeconds() + period  
    })  
  }  
  
  public unsubscribe (alias: string): void {  
    this.subscribers = this.subscribers.filter((subscriber) => subscriber.alias !== alias)  
  }  
  
  public exists (alias: string): boolean {  
    return this.subscribers.some((subscriber) => subscriber.alias === alias)  
  }  
  
  private setTimeout () {  
    this.timeout = setTimeout(() => {  
      const timestamp = Observer.getCurrentTimestampInSeconds()  
      this.subscribers.forEach((subscriber: ObserverSubscriber) => {  
        if (subscriber.nextCall <= timestamp) {  
          subscriber.callback(timestamp)  
          subscriber.nextCall = timestamp + subscriber.period  
  }  
      })  
  
      this.setTimeout()  
    }, 1000)  
  }  
  
  private static getCurrentTimestampInSeconds (): number {  
    return Math.round(new Date().getTime() / 1000)  
  }  
}  
  
const observer = new Observer()  
  
export default observer
```

### Vue:
```html
<template>
  <slot v-if="!needActiveLocationPermission" />
  <div class="location-error-wrapper" v-if="needActiveLocationPermission">
    <Card classes="location-error">
      <template v-slot:title>
        {{ translate('title') }}
      </template>
      <p>{{ translate('description') }}</p>
      <div class="location-actions">
        <MainButton :text="translate('check_again')" classes="check-again" @click="getLocation" />
      </div>
    </Card>
  </div>
</template>

<script lang="ts">
import { Options } from 'vue-class-component'
import { createMapper } from 'vuex-smart-module'

import observer from '@/libraries/observer'
import { LOCATION_OBSERVER_NAME } from '@/constants'

import { Position } from '@/types/libraries/Position'
import { WorkShiftType } from '@/types/entities/WorkShiftType'

import TranslatableClassComponent from '@/components/abstaract/TranslatableClassComponent'

import Card from '@/components/base/cards/Card.vue'
import MainButton from '@/components/base/buttons/MainButton.vue'

import Auth from '@/store/Auth'
import Config from '@/store/Config'
import Location from '@/store/Location'
import WorkShift from '@/store/WorkShift'

@Options({
  data () {
    return {
      gotPatrolAreas: false,
      positionWatcher: undefined
    }
  },
  components: {
    MainButton,
    Card
  },
  methods: {
    ...createMapper(Location).mapActions({
      getLocation: 'getLocation',
      getPatrolAreas: 'getPatrolAreas',
      setLocation: 'setLocation'
    }),
    ...createMapper(Location).mapMutations({
      setNeedActiveLocationPermission: 'setNeedActiveLocationPermission'
    }),
    subscribeToLocation () {
      observer.subscribe(this.setLocationIfNeeded, LOCATION_OBSERVER_NAME, this.updateCoordinatesPeriod)
      this.positionWatcher = navigator.geolocation.watchPosition(this.updateLocation, (error) => {
        if (error.code === 1) {
          this.setNeedActiveLocationPermission(true)
        }
      })
    },
    unsubscribeFromLocation () {
      observer.unsubscribe(LOCATION_OBSERVER_NAME)
      navigator.geolocation.clearWatch(this.positionWatcher)
    }
  },
  computed: {
    ...createMapper(Auth).mapGetters({
      isAuth: 'isAuth'
    }),
    ...createMapper(Location).mapGetters({
      needActiveLocationPermission: 'needActiveLocationPermission'
    }),
    ...createMapper(Config).mapGetters({
      updateCoordinatesPeriod: 'updateCoordinatesPeriod',
      showMap: 'showMap'
    }),
    ...createMapper(WorkShift).mapGetters({
      workShift: 'workShift'
    })
  },
  created () {
    if (!this.workShift) {
      return
    }

    this.subscribeToLocation()

    if (this.showMap) {
      this.getPatrolAreas()
      this.gotPatrolAreas = true
    }
  },
  watch: {
    workShift (nextWorkShift?: WorkShiftType, prevWorkShift?: WorkShiftType) {
      if (nextWorkShift && !prevWorkShift) {
        this.subscribeToLocation()

        if (this.showMap) {
          this.getPatrolAreas()
          this.gotPatrolAreas = true
        }
      }

      if (!nextWorkShift && prevWorkShift) {
        this.unsubscribeFromLocation()
      }
    },
    showMap (showMap: boolean) {
      if (showMap && !this.gotPatrolAreas) {
        this.getPatrolAreas()
        this.gotPatrolAreas = true
      }
    }
  }
})

export default class ConfigProvider extends TranslatableClassComponent {
  translationGroup = 'squad_location_detect_error_screen';

  currentPosition?: Position;
  lastSentPosition?: Position;

  setLocation!: (position: Position) => Promise<Position>;

  updateLocation (geoLocationPosition: GeolocationPosition) {
    this.currentPosition = {
      lat: geoLocationPosition.coords.latitude,
      lng: geoLocationPosition.coords.longitude,
      accuracy: geoLocationPosition.coords.accuracy
    }
  }

  setLocationIfNeeded () {
    const position = this.currentPosition
    if (!position || JSON.stringify(position) === JSON.stringify(this.lastSentPosition)) {
      return
    }

    this.setLocation(position).then(() => {
      this.lastSentPosition = position
    })
  }
}
</script>

<style lang="scss">
.location-error-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  width: 100%;

  .location-error {
    margin: 20px;

    .location-actions {
      width: 100%;
      text-align: center;
    }
  }
}
</style>

```

### Laravel:
```php
<?php

namespace App\Traits\Model;

use App\Storages\GeoStorage;
use Illuminate\Support\Arr;

trait RedisBaseTrait
{
    private ?array $redisBase = null;

    private bool $redisFetched = false;
    private bool $redisChanged = false;
    private bool $saveChangedRedisBase = true;

    public static function deleteBaseDataFromRedisById(int $id): void
    {
        static::getGeoStorage()->deleteByKey(static::getRedisKeyById($id));
    }

    public function getRedisBaseAttribute(): array
    {
        if ($this->redisFetched) {
            return $this->redisBase;
        }

        if (empty($this->id)) {
            $this->redisBase = [];
        } else {
            $this->redisBase = static::getGeoStorage()->getByKey(static::getRedisKeyById($this->id)) ?? [];
        }

        $this->redisFetched = true;

        return $this->redisBase;
    }

    public function setRedisBaseAttribute(array $redisBase): void
    {
        $this->redisBase = $redisBase;

        $this->redisFetched = true;
        $this->redisChanged = true;
    }

    public function updateRedisData(string $key, $value): void
    {
        if (!$this->redisFetched) {
            $this->getRedisBaseAttribute();
        }

        Arr::set($this->redisBase, $key, $value);
        $this->redisChanged = true;
    }

    public function clearRedisBase():void
    {
        $this->redisBase = null;
        $this->redisChanged = false;
        $this->redisFetched = false;
    }

    public function saveAndClearRedisBase(): void
    {
        if (!$this->redisChanged || !$this->saveChangedRedisBase || empty($this->id)) {
            return;
        }

        static::getGeoStorage()->setByKey(static::getRedisKeyById($this->id), $this->redisBase);

        $this->clearRedisBase();
    }

    public function deleteBaseDataFromRedis(): void
    {
        if (empty($this->id)) {
            return;
        }

        $this->saveChangedRedisBase = false;
        static::deleteBaseDataFromRedisById($this->id);
    }

    public function __destruct()
    {
        $this->saveAndClearRedisBase();
    }

    private static function getGeoStorage(): GeoStorage
    {
        return app()->make(GeoStorage::class);
    }

    private static function getRedisKeyById(int $id): string
    {
        return static::$redisKeyPrefix . "{$id}";
    }
}

```
