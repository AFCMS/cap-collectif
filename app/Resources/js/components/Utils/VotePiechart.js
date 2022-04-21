import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';

const VotePiechart = React.createClass({
  propTypes: {
    ok: PropTypes.number,
    nok: PropTypes.number,
    mitige: PropTypes.number,
    height: PropTypes.string,
    width: PropTypes.string,
    top: PropTypes.number,
    left: PropTypes.number,
  },
  mixins: [IntlMixin],

  getDefaultProps() {
    return {
      ok: 0,
      nok: 0,
      mitige: 0,
      height: '100%',
      width: '100%',
      top: 0,
      left: 0,
    };
  },

  render() {
    const { ok, mitige, nok, left, top, height, width } = this.props;
    if (!__SERVER__ && ok + mitige + nok > 0) {
      const Chart = require('react-google-charts').Chart;
      return (
        <div className="opinion__chart" style={{ textAlign: 'center' }}>
        <Chart
          chartType="PieChart"
          data={[
              [{ type: 'string' }, { type: 'number' }],
              [this.getIntlMessage('vote.ok'), ok],
              [this.getIntlMessage('vote.mitige'), mitige],
              [this.getIntlMessage('vote.nok'), nok],
          ]}
          height={height}
          width={width}
          options={{
            legend: 'none',
            chartArea: {
              left,
              top,
              width: '100%',
              height: '85%',
            },
            colors: ['#5cb85c', '#f0ad4e', '#d9534f'],
            pieSliceText: 'value',
            backgroundColor: 'transparent',
          }}
        />
        </div>
      );
    }
    return null;
  },
});

export default VotePiechart;
