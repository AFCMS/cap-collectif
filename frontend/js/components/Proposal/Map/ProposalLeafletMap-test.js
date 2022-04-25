// @flow
/* eslint-env jest */
import React from 'react';
import { shallow } from 'enzyme';
import { ProposalLeafletMap } from './ProposalLeafletMap';
import { $fragmentRefs, $refType } from '../../../mocks';

describe('<ProposalLeafletMap />', () => {
  const defaultMapOptions = {
    center: { lat: 48.8586047, lng: 2.3137325 },
    zoom: 12,
  };

  const proposals = [
    {
      id: 'pid1',
      $fragmentRefs,
      $refType,
      address: {
        lat: 49.8397,
        lng: 24.0297,
      },
    },
    {
      id: 'pid2',

      $fragmentRefs,
      $refType,
      address: {
        lat: 52.2297,
        lng: 21.0122,
      },
    },
    { id: 'pid3', $fragmentRefs, $refType, address: null },
    {
      id: 'pid4',
      $fragmentRefs,
      $refType,
      address: {
        lat: 51.5074,
        lng: -0.0901,
      },
    },
  ];

  const mapTokens = {
    MAPBOX: {
      initialPublicToken:
        '***REMOVED***',
      publicToken:
        '***REMOVED***',
      styleOwner: 'capcollectif',
      styleId: '***REMOVED***',
    },
  };

  it('should render a map with only valid markers', () => {
    const wrapper = shallow(
      <ProposalLeafletMap
        defaultMapOptions={defaultMapOptions}
        visible
        mapTokens={mapTokens}
        proposals={proposals}
      />,
    );
    const popup = wrapper.find('ProposalLeafletMapstyle__BlankPopup');
    expect(wrapper).toMatchSnapshot();
    expect(popup).toHaveLength(3);
  });

  it('should not render a map with visible = false', () => {
    const wrapper = shallow(
      <ProposalLeafletMap
        defaultMapOptions={defaultMapOptions}
        visible={false}
        mapTokens={mapTokens}
        proposals={proposals}
      />,
    );
    expect(wrapper).toMatchSnapshot();
  });
});
