// @flow
import GeoJsonGeometriesLookup from 'geojson-geometries-lookup';
import warning from '~/utils/warning';
import type { MapCenterObject } from '~/components/Proposal/Map/Map.types';

export type Style = {|
  +border?: ?{|
    +id: ?string,
    +enabled: boolean,
    +color: ?string,
    +opacity: ?number,
    +size: ?number,
  |},
  +background?: ?{|
    +id: ?string,
    +enabled: boolean,
    +color: ?string,
    +opacity: ?number,
    +size: ?number,
  |},
|};

export type GeoJson = {|
  district: string,
  style: Style,
|};

export const geoContains = (geoJSONS: Array<GeoJson>, pos: MapCenterObject) => {
  if (!pos || !geoJSONS.length) return false;
  return geoJSONS.some(geoJSON => {
    const glookup = new GeoJsonGeometriesLookup(geoJSON.district);
    const point = { type: 'Point', coordinates: [pos.lng, pos.lat] };
    return glookup.hasContainers(point);
  });
};

type District = {|
  +geojson: ?string,
  +id: string,
  +displayedOnMap: boolean,
  +name?: string,
  +border?: ?{|
    +id: ?string,
    +enabled: boolean,
    +color: ?string,
    +opacity: ?number,
    +size: ?number,
  |},
  +background?: ?{|
    +id: ?string,
    +enabled: boolean,
    +color: ?string,
    +opacity: ?number,
    +size: ?number,
  |},
|};

const parseGeoJson = (district: District) => {
  const { geojson, id } = district;
  try {
    return JSON.parse(geojson || '');
  } catch (e) {
    warning(
      false,
      `Using empty geojson for ${id} because we couldn't parse the geojson : ${geojson || ''}`,
    );
    return null;
  }
};

export const formatGeoJsons = (districts: $ReadOnlyArray<District>) => {
  let geoJsons = [];
  if (districts) {
    geoJsons = districts
      .filter(d => d.geojson && d.displayedOnMap)
      .map<GeoJson>(d => ({
        // $FlowFixMe geojson is a non-null string, don't worry Flow
        district: parseGeoJson(d),
        style: {
          border: d.border,
          background: d.background,
        },
      }));
  }
  return geoJsons;
};
