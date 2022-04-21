// @flow
import { MapControl } from 'react-leaflet';
import { intlShape, injectIntl } from 'react-intl';
import L from 'leaflet';
import 'leaflet.locatecontrol/dist/L.Control.Locate.min';

export class LocateControl extends MapControl {
  componentWillMount() {
    const { intl } = this.props;
    this.leafletElement = L.control.locate({
      position: 'topleft',
      flyTo: true,
      icon: 'cap-map-target-1',
      strings: {
        title: intl.formatMessage({ id: 'proposal.map.form.control.title' }),
        metersUnit: intl.formatMessage({
          id: 'proposal.map.form.control.metersUnit',
        }),
        feetUnit: intl.formatMessage({
          id: 'proposal.map.form.control.feetUnit',
        }),
        popup: intl.formatMessage({
          id: 'proposal.map.form.control.popup',
          values: {},
        }),
        outsideMapBoundsMsg: intl.formatMessage({
          id: 'proposal.map.form.control.outsideMapBoundsMsg',
        }),
      },
      drawCircle: false,
    });
  }
}

LocateControl.propTypes = {
  intl: intlShape.isRequired,
};

export default injectIntl(LocateControl);
