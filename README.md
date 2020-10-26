# Eth2 Data Research Hub

Welcome! This project was put together by Elias Simos and Sid Shekhar, answering the Ethereum Foundation's call for submission towards the Medalla data challenge.

:sparkles: eth2data.github.io :sparkles:

While we started with Medalla, our hope is to continue working towards enriching the ecosystem with more research and metrics, as Phase 0 of eth2 rolls out.

In our view, good measurement of key parameters, leads to better understanding of live networks and an overall a healthier ecosystem.

:seedling:

## Contents
In this repo, you will find:
- `jupyter notebooks` that power all metrics
- Raw data from the Medalla Testnet in .csv
- Scripts to run eth2 nodes and plug onto the beaconcha.in API
- Synching  logs from all four  main eth2 clients

We have tried to keep the notebook titles and content, as self-explanatory as possible, but we will keep updating and polishing over time.

## Installation

We are using the latest version of Python Anaconda to power the notebooks - see how to install [here](https://docs.anaconda.com/). 
```bash
bash ~/Downloads/Anaconda3-2020.02-MacOSX-x86_64.s
```
We also recommend installing Jupyter Lab to run the notebooks - with `conda`,

```conda
conda install -c conda-forge jupyterlab
```

or `pip`!
```pip
pip install jupyterlab
```

## Data

We have uploaded the lightest datasets under the "data" folder in this repo.

For the brave among you that want to tackle the `slot_summary_stats` and the big `attestations` datasets, we have uploaded those [here](https://drive.google.com/drive/folders/1SfVJcb2CbkVCDu0ytxCoYqlPwwR3ECvc?usp=sharing).

The attestations dataset is broken up in pieces, but we have provisioned code to merge it together and check for validity under `attestations_merge.ipynb`.

To get acquainted with the analysis logic, we suggest first running a sandboxed version of the `attestations_aggregation_finality_analysis.ipynb` on a small subset of the attestations dataset - which you will find in the `data` folder under `atts_50k_rows.csv`.

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change!

What we are particularly interested in is calculating the delta between the slashable offences enforced, and those committed. You will find a good library of code to get you going under the `attestations_aggregation_finality_analysis.ipynb`.

## License
[MIT](https://choosealicense.com/licenses/mit/)
